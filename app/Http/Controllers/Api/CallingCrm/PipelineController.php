<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\StageTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $pipelines = Pipeline::with(['stages.tags', 'stages.transitions'])
            ->when($request->boolean('active_only'), fn ($query) => $query->active())
            ->ordered()
            ->get();

        return response()->json(['status' => true, 'data' => $pipelines]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePipeline($request);
        $pipeline = DB::transaction(function () use ($data) {
            $pipeline = Pipeline::create($data);
            $this->createDefaultStages($pipeline);

            return $pipeline;
        });

        return response()->json([
            'status' => true,
            'message' => 'Pipeline created successfully',
            'data' => $pipeline->load(['stages.tags', 'stages.transitions']),
        ], 201);
    }

    public function show(Pipeline $pipeline)
    {
        return response()->json([
            'status' => true,
            'data' => $pipeline->load(['stages.tags', 'stages.transitions', 'campaigns', 'dispositions']),
        ]);
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $pipeline->update($this->validatePipeline($request));

        return response()->json([
            'status' => true,
            'message' => 'Pipeline updated successfully',
            'data' => $pipeline->fresh(['stages.tags', 'stages.transitions']),
        ]);
    }

    public function destroy(Pipeline $pipeline)
    {
        $pipeline->delete();

        return response()->json(['status' => true, 'message' => 'Pipeline deleted successfully']);
    }

    public function storeStage(Request $request, Pipeline $pipeline)
    {
        $data = $this->validateStage($request);
        $data['pipeline_id'] = $pipeline->id;
        $data['code'] = $data['code'] ?? Str::slug($data['name'], '_');
        $data['sort_order'] = $data['sort_order'] ?? ($pipeline->stages()->max('sort_order') + 1);

        $stage = DB::transaction(function () use ($data, $pipeline) {
            $pipeline->stages()
                ->where('sort_order', '>=', $data['sort_order'])
                ->increment('sort_order');

            return LeadStage::create($data);
        });

        return response()->json([
            'status' => true,
            'message' => 'Stage created successfully',
            'data' => $stage,
        ], 201);
    }

    public function updateStage(Request $request, LeadStage $stage)
    {
        $data = $this->validateStage($request);
        $data['code'] = $data['code'] ?? Str::slug($data['name'], '_');
        $stage->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Stage updated successfully',
            'data' => $stage->fresh(['tags', 'transitions']),
        ]);
    }

    public function updateStageTransitions(Request $request, LeadStage $stage)
    {
        $data = $request->validate([
            'transition_ids' => ['present', 'array'],
            'transition_ids.*' => ['integer', 'distinct', 'exists:lead_stages,id'],
        ]);

        $transitionIds = LeadStage::query()
            ->where('pipeline_id', $stage->pipeline_id)
            ->where('id', '!=', $stage->id)
            ->whereIn('id', $data['transition_ids'])
            ->pluck('id');

        abort_unless($transitionIds->count() === count($data['transition_ids']), 422, 'Transitions must stay inside the selected pipeline.');

        $stage->transitions()->sync($transitionIds);

        return response()->json([
            'status' => true,
            'message' => 'Stage transitions updated successfully',
            'data' => $stage->fresh(['tags', 'transitions']),
        ]);
    }

    public function destroyStage(LeadStage $stage)
    {
        $stage->tags()->delete();
        $stage->transitions()->detach();
        $stage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Stage deleted successfully',
        ]);
    }

    public function reorderStages(Request $request, Pipeline $pipeline)
    {
        $data = $request->validate([
            'stages' => ['required', 'array'],
            'stages.*.id' => ['required', 'exists:lead_stages,id'],
            'stages.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['stages'] as $stageData) {
            LeadStage::where('pipeline_id', $pipeline->id)
                ->where('id', $stageData['id'])
                ->update(['sort_order' => $stageData['sort_order']]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Stages reordered successfully',
            'data' => $pipeline->fresh(['stages.tags', 'stages.transitions']),
        ]);
    }

    public function storeTag(Request $request, LeadStage $stage)
    {
        $data = $this->validateTag($request);
        $data['sort_order'] = $data['sort_order'] ?? ($stage->tags()->max('sort_order') + 1);

        $tag = $stage->tags()->create($data);

        return response()->json([
            'status' => true,
            'message' => 'Stage tag created successfully',
            'data' => $tag,
        ], 201);
    }

    public function updateTag(Request $request, StageTag $tag)
    {
        $tag->update($this->validateTag($request));

        return response()->json([
            'status' => true,
            'message' => 'Stage tag updated successfully',
            'data' => $tag->fresh(),
        ]);
    }

    public function destroyTag(StageTag $tag)
    {
        $tag->delete();

        return response()->json(['status' => true, 'message' => 'Stage tag deleted successfully']);
    }

    private function validatePipeline(Request $request): array
    {
        return $request->validate([
            'business_profile_id' => ['nullable', 'exists:crm_business_profiles,id'],
            'name' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:7'],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }

    private function validateStage(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:80'],
            'category' => ['required', Rule::in(['fresh', 'in_progress', 'closed_won', 'closed_lost'])],
            'color' => ['nullable', 'string', 'max:7'],
            'is_closed' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function createDefaultStages(Pipeline $pipeline): void
    {
        $stages = [
            ['name' => 'OPEN', 'code' => 'open', 'category' => 'fresh', 'color' => '#763abb'],
            ['name' => 'IN PROGRESS', 'code' => 'in_progress', 'category' => 'in_progress', 'color' => '#763abb'],
            ['name' => 'CONVERTED', 'code' => 'converted', 'category' => 'closed_won', 'color' => '#00ae68', 'is_closed' => true],
            ['name' => 'LOST', 'code' => 'lost', 'category' => 'closed_lost', 'color' => '#ff3131', 'is_closed' => true],
        ];

        $createdStages = collect($stages)->map(function (array $stage, int $index) use ($pipeline) {
            return $pipeline->stages()->create($stage + [
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        });

        $closedStageIds = $createdStages
            ->where('is_closed', true)
            ->pluck('id');

        $createdStages->where('is_closed', false)->each(function (LeadStage $stage) use ($createdStages, $closedStageIds) {
            $forwardOpenStageIds = $createdStages
                ->where('is_closed', false)
                ->where('sort_order', '>', $stage->sort_order)
                ->pluck('id');

            $stage->transitions()->sync($forwardOpenStageIds->merge($closedStageIds));
        });
    }

    private function validateTag(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
