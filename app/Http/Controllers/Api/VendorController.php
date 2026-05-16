<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * Fetch complete vendor profile data including personal form details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfileData(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Get user data
            $user = User::findOrFail($userId);

            // Get vendor detail data
            $vendorDetail = VendorDetail::where('user_id', $userId)->first();

            // Prepare response data
            $profileData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'status' => $user->status,
                    'approval_status' => $user->approval_status,
                ],
                'vendor_detail' => $vendorDetail ? [
                    'id' => $vendorDetail->id,
                    'user_id' => $vendorDetail->user_id,
                    'firm_name' => $vendorDetail->firm_name,
                    'gst_number' => $vendorDetail->gst_number,
                    'license_type' => $vendorDetail->license_type,
                    'fertilizer_license_no' => $vendorDetail->fertilizer_license_no,
                    'seeds_license_no' => $vendorDetail->seeds_license_no,
                    'pesticides_license_no' => $vendorDetail->pesticides_license_no,
                    'gst_doc' => $vendorDetail->gst_doc,
                    'license_doc' => $vendorDetail->license_doc,
                    'address' => $vendorDetail->address,
                    'near_transport_indore' => $vendorDetail->near_transport_indore,
                    'phone_number' => $vendorDetail->phone_number,
                    'alternate_number' => $vendorDetail->alternate_number,
                    'aadhar_front_path' => $vendorDetail->aadhar_front_path,
                    'aadhar_back_path' => $vendorDetail->aadhar_back_path,
                    'created_at' => $vendorDetail->created_at,
                    'updated_at' => $vendorDetail->updated_at,
                ] : null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Vendor profile data retrieved successfully',
                'data' => $profileData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor profile data: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve vendor profile data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get only personal form data (vendor details)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPersonalFormData(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Get vendor detail data
            $vendorDetail = VendorDetail::where('user_id', $userId)->firstOrFail();

            return response()->json([
                'status' => true,
                'message' => 'Personal form data retrieved successfully',
                'data' => $vendorDetail,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching personal form data: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Vendor details not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update vendor profile data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileData(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Validate input
            $validated = $request->validate([
                'firm_name' => 'nullable|string|max:255',
                'gst_number' => 'nullable|string|max:50',
                'license_type' => 'nullable|string|max:100',
                'fertilizer_license_no' => 'nullable|string|max:100',
                'seeds_license_no' => 'nullable|string|max:100',
                'pesticides_license_no' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:500',
                'near_transport_indore' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'alternate_number' => 'nullable|string|max:20',
            ]);

            // Update or create vendor detail
            $vendorDetail = VendorDetail::updateOrCreate(
                ['user_id' => $userId],
                $validated
            );

            return response()->json([
                'status' => true,
                'message' => 'Vendor profile updated successfully',
                'data' => $vendorDetail,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating vendor profile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update vendor profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
