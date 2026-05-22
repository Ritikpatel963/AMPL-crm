# Calling CRM Backend Specification

This document is derived from the current Calling CRM frontend under `resources/views/admin_panel/callingcrm`. The frontend is currently mostly static Blade UI, but it clearly implies a full calling CRM backend similar to Neodove: lead upload, campaign management, user/agent management, calling, dispositions, follow-ups, retry logic, dashboards, reports, and role-based access control.

## 1. Frontend Modules Studied

| Frontend page | Route name | Current path | Main features implied |
|---|---|---|---|
| Dashboard | `admin_panel.admin.callingcrm.dashboard` | `/admin_panel/admin/calling-crm` | call overview, agent activity, leads by stage, pinned campaigns, upload Excel, create campaign |
| Contacts | `admin_panel.admin.callingcrm.contact` | `/admin_panel/admin/calling-crm/contact` | lead creation/search form, custom fields, campaign selection, source filters, Excel upload, add lead modal |
| Contact Properties | `admin_panel.admin.callingcrm.contact.properties` | `/admin_panel/admin/calling-crm/contact/properties` | custom lead property CRUD, property activation |
| Pipeline | `admin_panel.admin.callingcrm.pipeline` | `/admin_panel/admin/calling-crm/pipeline` | pipelines, campaigns, lead funnel, lead summary, uploaded file details, edit lead, history, bulk actions |
| Reports | `admin_panel.admin.callingcrm.report` | `/admin_panel/admin/calling-crm/report` | report catalog and report downloads |
| User Report | `admin_panel.admin.callingcrm.report.user` | `/admin_panel/admin/calling-crm/report/user` | agent call metrics, follow-up metrics, break metrics, messaging metrics |
| Login Report | `admin_panel.admin.callingcrm.report.login` | `/admin_panel/admin/calling-crm/report/login` | hourly report, login report, day report |
| Trends | `admin_panel.admin.callingcrm.trends` | `/admin_panel/admin/calling-crm/trends` | trend widgets and charts for calls, conversions, duration, lead sources, lost leads |
| Settings | `admin_panel.admin.callingcrm.settings` | `/admin_panel/admin/calling-crm/settings` | users, pipelines, profile, roles, retry settings, lead priority, custom properties |

## 2. Core Roles And Permissions

### Roles

| Role | Purpose |
|---|---|
| `super_admin` | Full system ownership, tenant/business setup, all CRM settings |
| `admin` | Manage users, campaigns, reports, settings, imports, and all leads |
| `team_lead` | Manage assigned agents, reassign leads, view team reports |
| `executive` | Call assigned leads, update dispositions, schedule follow-ups |
| `auditor` | Read-only reporting and call log access |

### Permissions

Recommended permission keys:

- `crm.dashboard.view`
- `crm.users.manage`
- `crm.roles.manage`
- `crm.pipelines.manage`
- `crm.campaigns.view`
- `crm.campaigns.manage`
- `crm.leads.view`
- `crm.leads.create`
- `crm.leads.update`
- `crm.leads.delete`
- `crm.leads.assign`
- `crm.leads.bulk_actions`
- `crm.calls.start`
- `crm.calls.view`
- `crm.dispositions.manage`
- `crm.followups.manage`
- `crm.imports.manage`
- `crm.reports.view`
- `crm.reports.export`
- `crm.settings.manage`

All API endpoints below require authentication. Authorization should be checked by permission plus ownership scope.

## 3. Entity Relationship Summary

- Business profile has many users, pipelines, custom properties, lead sources, imports, and campaigns.
- User belongs to one role and may report to another user.
- Team has many users through team memberships.
- Pipeline has many lead stages, dispositions, retry settings, and campaigns.
- Campaign belongs to a pipeline and has many assigned agents through a pivot table.
- Campaign has many leads and many uploaded contact lists.
- Lead belongs to campaign, pipeline stage, assigned user, source, and optional uploaded file row.
- Lead has many phone numbers, property values, notes, call logs, dispositions, follow-ups, timeline events, and communication events.
- Call log belongs to lead, user, campaign, optional disposition, and telephony provider call record.
- Follow-up belongs to lead, user, campaign, and optional call log/disposition.
- Uploaded contact list has many import rows, and accepted rows create or update leads.
- Reports are generated from calls, leads, sessions, follow-ups, dispositions, and communication events.

## 4. Database Schema

Use `BIGINT UNSIGNED` auto-increment IDs unless UUIDs are already standard in the parent app. All tables should include `created_at`, `updated_at`, and `deleted_at` unless noted.

### 4.1 `crm_business_profiles`

Stores the Calling CRM company profile shown in Settings > Profile.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `business_name` | string(160) | no | | e.g. AMPL AGRO MARKET PRIVATE LIMITED |
| `phone` | string(20) | no | | indexed |
| `address` | text | yes | | max 250 in UI |
| `state` | string(80) | yes | | |
| `pincode` | string(12) | yes | | |
| `gst_number` | string(32) | yes | | indexed, nullable unique |
| `working_days` | enum | no | `mon_sat` | `mon_sat`, `mon_fri`, `all_days`, `custom` |
| `work_start_time` | time | yes | | |
| `work_end_time` | time | yes | | |
| `timezone` | string(64) | no | `Asia/Kolkata` | |
| `settings` | json | yes | | feature flags, telephony config references |

Indexes: `phone`, `gst_number`.

### 4.2 `users`

Use the existing app `users` table and add CRM-specific columns if missing.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `name` | string(120) | no | | |
| `email` | string(160) | yes | | unique when present |
| `phone` | string(20) | no | | unique |
| `password` | string | no | | hashed |
| `employee_id` | string(80) | yes | | indexed |
| `reporting_manager_id` | foreignId(users) | yes | | self reference |
| `crm_status` | enum | no | `active` | `active`, `inactive`, `deactivated`, `deleted` |
| `lead_assignment_enabled` | boolean | no | true | used by "Disable Lead Assignment" |
| `expires_at` | date | yes | | UI shows Expiry Date |
| `last_seen_at` | timestamp | yes | | |

Indexes: `phone`, `email`, `reporting_manager_id`, `crm_status`, `lead_assignment_enabled`.

### 4.3 `roles`, `permissions`, `role_permission`, `user_role`

Use existing RBAC tables if already present. If not, create standard tables.

`roles`: `id`, `name`, `slug`, `description`, `is_system`, timestamps, soft deletes.

`permissions`: `id`, `name`, `slug`, `module`, timestamps.

`role_permission`: `role_id`, `permission_id`, unique pair.

`user_role`: `user_id`, `role_id`, unique pair.

### 4.4 `crm_teams`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `name` | string(120) | no | | |
| `team_lead_id` | foreignId(users) | yes | | |
| `description` | text | yes | | |
| `is_active` | boolean | no | true | |

### 4.5 `crm_team_user`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `team_id` | foreignId(crm_teams) | no | | cascade delete |
| `user_id` | foreignId(users) | no | | cascade delete |
| `role_in_team` | enum | no | `agent` | `lead`, `agent`, `observer` |

Unique: `team_id,user_id`.

### 4.6 `crm_pipelines`

Extends current `pipelines` migration.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `business_profile_id` | foreignId(crm_business_profiles) | yes | | nullable for single tenant |
| `name` | string(120) | no | | unique per business |
| `color` | string(7) | no | `#763abb` | UI color selector |
| `description` | text | yes | | |
| `is_default` | boolean | no | false | |
| `is_active` | boolean | no | true | |
| `sort_order` | unsignedInteger | no | 0 | |

Indexes: `business_profile_id,name`, `is_active`, `sort_order`.

### 4.7 `crm_lead_stages`

Extends current `lead_stages`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `pipeline_id` | foreignId(crm_pipelines) | no | | |
| `name` | string(120) | no | | e.g. Fresh Leads |
| `code` | string(80) | yes | | slug |
| `category` | enum | no | `in_progress` | `fresh`, `in_progress`, `closed_won`, `closed_lost` |
| `color` | string(7) | no | `#0f766e` | |
| `is_closed` | boolean | no | false | true for won/lost |
| `sort_order` | unsignedInteger | no | 0 | |
| `is_active` | boolean | no | true | |

Unique: `pipeline_id,name`.

Default stages implied by UI:

- `Fresh Leads`
- `Follow Up (Mandatory)`
- `Catalog and Price Shared`
- `Negotiation or Price Issue`
- `Closed Won`
- `Closed Lost`

### 4.8 `crm_stage_tags`

Tags attached to stages, shown in pipeline settings and lead forms.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `stage_id` | foreignId(crm_lead_stages) | no | | |
| `name` | string(120) | no | | e.g. Future Requirement |
| `color` | string(7) | yes | | |
| `sort_order` | unsignedInteger | no | 0 | |
| `is_active` | boolean | no | true | |

Default examples: `No Season`, `Future Requirement`, `Out of Station`, `No Response`, `Call Back`, `Catalog Sent`, `Price Shared`, `Discount Request`, `Price Issue`, `Converted`, `Not Interested`.

### 4.9 `crm_campaigns`

Extends current `campaigns`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `pipeline_id` | foreignId(crm_pipelines) | no | | |
| `manager_id` | foreignId(users) | yes | | campaign manager |
| `name` | string(160) | no | | indexed |
| `description` | text | yes | | |
| `status` | enum | no | `active` | `draft`, `active`, `paused`, `completed`, `archived` |
| `distribution` | enum | no | `on_demand` | `on_demand`, `equal`, `conditional`, `auto_assign` |
| `priority` | enum | no | `medium` | `low`, `medium`, `high`, `critical` |
| `is_pinned` | boolean | no | false | dashboard pinned campaigns |
| `hide_paused_from_agents` | boolean | no | false | |
| `lead_chunk_size` | unsignedSmallInteger | no | 10 | UI says on-demand assigns 10 leads |
| `starts_at` | timestamp | yes | | |
| `ends_at` | timestamp | yes | | |
| `settings` | json | yes | | conditional assignment rules, dialer options |

Indexes: `pipeline_id,status`, `manager_id`, `priority`, `is_pinned`.

### 4.10 `crm_campaign_user`

Campaign-agent assignment.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `user_id` | foreignId(users) | no | | |
| `role` | enum | no | `agent` | `manager`, `agent`, `viewer` |
| `assigned_leads_count` | unsignedInteger | no | 0 | denormalized for reports |
| `is_active` | boolean | no | true | |

Unique: `campaign_id,user_id`.

### 4.11 `crm_lead_sources`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `name` | string(120) | no | | |
| `code` | enum/string | no | | `FILE_UPLOAD`, `WALK_IN_LEAD`, `INCOMING_IVR`, `WORKFLOW`, `GOOGLE_SHEET`, `MANUAL`, `API`, `WEBHOOK` |
| `is_active` | boolean | no | true | |

Unique: `code`.

### 4.12 `crm_contact_lists`

Represents uploaded Excel/CSV files shown as `MP Data.xlsx/Existing Cx`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `uploaded_by` | foreignId(users) | no | | |
| `file_name` | string(255) | no | | |
| `sheet_name` | string(160) | yes | | |
| `storage_path` | string(500) | no | | |
| `mime_type` | string(120) | yes | | |
| `file_size` | unsignedInteger | no | 0 | max 3 MB in UI |
| `total_rows` | unsignedInteger | no | 0 | max 25,000 in UI |
| `created_rows` | unsignedInteger | no | 0 | |
| `merged_rows` | unsignedInteger | no | 0 | |
| `failed_rows` | unsignedInteger | no | 0 | |
| `status` | enum | no | `queued` | `queued`, `processing`, `completed`, `failed`, `partially_failed` |
| `mapping` | json | yes | | column mapping |
| `processed_at` | timestamp | yes | | |

Indexes: `campaign_id,status`, `uploaded_by`, `created_at`.

### 4.13 `crm_contact_list_rows`

Rows imported from a file and their validation status.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `contact_list_id` | foreignId(crm_contact_lists) | no | | |
| `lead_id` | foreignId(crm_leads) | yes | | set after create/merge |
| `row_number` | unsignedInteger | no | | |
| `raw_payload` | json | no | | original row |
| `status` | enum | no | `created` | `created`, `merged`, `merged_reopened`, `failed`, `skipped` |
| `failure_reason` | string(255) | yes | | e.g. phone number invalid |

Indexes: `contact_list_id,status`, `lead_id`, unique `contact_list_id,row_number`.

### 4.14 `crm_leads`

Extends current `leads` table.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `pipeline_id` | foreignId(crm_pipelines) | no | | denormalized from campaign |
| `stage_id` | foreignId(crm_lead_stages) | yes | | |
| `tag_id` | foreignId(crm_stage_tags) | yes | | |
| `assigned_user_id` | foreignId(users) | yes | | current assignee |
| `source_id` | foreignId(crm_lead_sources) | yes | | |
| `contact_list_id` | foreignId(crm_contact_lists) | yes | | |
| `name` | string(180) | yes | | UI permits blank file names but manual should require |
| `primary_phone` | string(20) | no | | normalized E.164 where possible |
| `email` | string(180) | yes | | |
| `status` | enum | no | `uncontacted` | `uncontacted`, `in_progress`, `converted`, `lost`, `closed`, `reopened` |
| `priority_bucket` | enum | no | `normal` | `manual_scheduled`, `assigned_uncontacted`, `unassigned_uncontacted`, `in_progress_no_followup`, `not_connected_scheduled`, `normal` |
| `deal_amount` | decimal(14,2) | yes | | |
| `currency` | string(3) | no | `INR` | |
| `last_call_at` | timestamp | yes | | indexed |
| `next_follow_up_at` | timestamp | yes | | indexed |
| `total_disposition_count` | unsignedInteger | no | 0 | |
| `confidential_remark` | text | yes | | |
| `metadata` | json | yes | | imported extra fields |

Indexes: `campaign_id,status`, `assigned_user_id,status`, `pipeline_id,stage_id`, `primary_phone`, `next_follow_up_at`, `last_call_at`.

Unique recommendation: `campaign_id,primary_phone` if the same number may exist in different campaigns; otherwise global unique `primary_phone`.

### 4.15 `crm_lead_phone_numbers`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `phone` | string(20) | no | | |
| `type` | enum | no | `alternate` | `primary`, `alternate`, `whatsapp` |
| `is_primary` | boolean | no | false | |
| `is_valid` | boolean | no | true | |

Unique: `lead_id,phone`.

### 4.16 `crm_contact_properties`

Extends current `contact_properties`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `business_profile_id` | foreignId(crm_business_profiles) | yes | | |
| `name` | string(60) | no | | UI max 60 |
| `slug` | string(80) | no | | |
| `data_type` | enum | no | `text` | `text`, `number`, `email`, `date`, `dropdown`, `boolean`, `textarea` |
| `options` | json | yes | | dropdown values |
| `is_required` | boolean | no | false | |
| `is_active` | boolean | no | true | |
| `sort_order` | unsignedInteger | no | 0 | |

Unique: `business_profile_id,slug`. Limit to 40 active custom properties per business as shown by UI.

Default properties from frontend: `Company Name`, `Address Line 1`, `Address Line 2`, `Town/City`, `State`, `Pincode`, `GST`.

### 4.17 `crm_lead_property_values`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `property_id` | foreignId(crm_contact_properties) | no | | |
| `value_text` | text | yes | | normalized string |
| `value_number` | decimal(16,4) | yes | | for numeric filters |
| `value_date` | date | yes | | for date filters |
| `value_json` | json | yes | | dropdown/multi values |

Unique: `lead_id,property_id`.

### 4.18 `crm_dispositions`

Extends current `dispositions`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `pipeline_id` | foreignId(crm_pipelines) | no | | |
| `stage_id` | foreignId(crm_lead_stages) | yes | | target stage |
| `tag_id` | foreignId(crm_stage_tags) | yes | | target tag |
| `name` | string(120) | no | | |
| `type` | enum | no | `in_progress` | `fresh`, `in_progress`, `closed_won`, `closed_lost`, `not_connected` |
| `requires_follow_up` | boolean | no | false | |
| `requires_note` | boolean | no | false | |
| `marks_lead_closed` | boolean | no | false | |
| `sort_order` | unsignedInteger | no | 0 | |
| `is_active` | boolean | no | true | |

### 4.19 `crm_call_logs`

Extends current `call_logs`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `user_id` | foreignId(users) | yes | | agent |
| `disposition_id` | foreignId(crm_dispositions) | yes | | |
| `direction` | enum | no | `outgoing` | `outgoing`, `incoming` |
| `status` | enum | no | `not_connected` | `initiated`, `ringing`, `connected`, `answered`, `not_connected`, `busy`, `no_answer`, `failed`, `missed` |
| `provider_call_id` | string(160) | yes | | indexed |
| `phone_number` | string(20) | no | | dialed number |
| `started_at` | timestamp | yes | | |
| `answered_at` | timestamp | yes | | |
| `ended_at` | timestamp | yes | | |
| `duration_seconds` | unsignedInteger | no | 0 | talk time |
| `ring_duration_seconds` | unsignedInteger | no | 0 | |
| `recording_url` | string(500) | yes | | |
| `notes` | text | yes | | |
| `metadata` | json | yes | | provider payload |

Indexes: `lead_id`, `campaign_id,started_at`, `user_id,started_at`, `status`, `provider_call_id`.

### 4.20 `crm_lead_dispositions`

Stores each lead dispose action, separate from raw call logs.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `call_log_id` | foreignId(crm_call_logs) | yes | | |
| `user_id` | foreignId(users) | no | | |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `from_stage_id` | foreignId(crm_lead_stages) | yes | | |
| `to_stage_id` | foreignId(crm_lead_stages) | yes | | |
| `tag_id` | foreignId(crm_stage_tags) | yes | | |
| `disposition_id` | foreignId(crm_dispositions) | yes | | |
| `call_status` | enum | no | `connected` | `connected`, `not_connected`, `missed`, `busy`, `no_answer`, `failed` |
| `remark` | text | yes | | |
| `disposed_at` | timestamp | no | | indexed |

### 4.21 `crm_follow_ups`

Extends current `follow_ups`.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `campaign_id` | foreignId(crm_campaigns) | no | | |
| `user_id` | foreignId(users) | yes | | assigned user |
| `created_by` | foreignId(users) | yes | | |
| `call_log_id` | foreignId(crm_call_logs) | yes | | |
| `scheduled_at` | timestamp | no | | |
| `completed_at` | timestamp | yes | | |
| `status` | enum | no | `scheduled` | `scheduled`, `due`, `completed`, `missed`, `cancelled`, `rescheduled` |
| `note` | text | yes | | |
| `is_system_generated` | boolean | no | false | retry logic |

Indexes: `user_id,scheduled_at,status`, `lead_id,status`, `campaign_id,scheduled_at`.

### 4.22 `crm_retry_reasons`

Reasons and retry setup shown in Settings > Retry Setting.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `pipeline_id` | foreignId(crm_pipelines) | yes | | null means global |
| `name` | string(120) | no | | e.g. Not Connected |
| `is_active` | boolean | no | true | |
| `sort_order` | unsignedInteger | no | 0 | |

### 4.23 `crm_retry_rules`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `retry_reason_id` | foreignId(crm_retry_reasons) | no | | |
| `logic_type` | enum | no | `fixed` | `fixed`, `variable` |
| `max_retries` | unsignedTinyInteger | no | 5 | UI max 99 |
| `interval_value` | unsignedTinyInteger | no | 1 | |
| `interval_unit` | enum | no | `hours` | `minutes`, `hours`, `days` |
| `mark_lost_after_exhausted` | boolean | no | true | UI note |
| `is_active` | boolean | no | true | |

### 4.24 `crm_lead_priority_rules`

Settings > Lead Priority drag ordering.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `business_profile_id` | foreignId(crm_business_profiles) | yes | | |
| `name` | string(120) | no | | |
| `code` | string(80) | no | | |
| `sort_order` | unsignedInteger | no | 0 | |
| `is_locked` | boolean | no | false | manual scheduled leads locked |
| `is_active` | boolean | no | true | |

Default order:

1. `Manually Scheduled Leads`
2. `Uncontacted Assigned Leads`
3. `Uncontacted Unassigned Leads`
4. `In-Progress without Follow-Up`
5. `Not Connected Scheduled`

### 4.25 `crm_user_sessions`

Extends current `user_sessions` for login/day/hourly reports.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `user_id` | foreignId(users) | no | | |
| `logged_in_at` | timestamp | no | | |
| `logged_out_at` | timestamp | yes | | |
| `status` | enum | no | `online` | `online`, `offline`, `expired` |
| `ip_address` | string(45) | yes | | |
| `user_agent` | string(500) | yes | | |
| `break_minutes` | unsignedInteger | no | 0 | denormalized |

Indexes: `user_id,logged_in_at`, `status`.

### 4.26 `crm_user_breaks`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `user_id` | foreignId(users) | no | | |
| `session_id` | foreignId(crm_user_sessions) | yes | | |
| `reason` | string(120) | yes | | |
| `started_at` | timestamp | no | | |
| `ended_at` | timestamp | yes | | |
| `duration_seconds` | unsignedInteger | no | 0 | |

### 4.27 `crm_notes`

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `user_id` | foreignId(users) | no | | |
| `note` | text | no | | |
| `visibility` | enum | no | `team` | `private`, `team`, `manager` |
| `is_confidential` | boolean | no | false | edit lead modal |

### 4.28 `crm_communication_events`

Tracks WhatsApp, email, SMS counts shown in reports/trends.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | yes | | |
| `campaign_id` | foreignId(crm_campaigns) | yes | | |
| `user_id` | foreignId(users) | yes | | |
| `channel` | enum | no | | `sms`, `email`, `whatsapp` |
| `direction` | enum | no | `outgoing` | `incoming`, `outgoing` |
| `status` | enum | no | `sent` | `queued`, `sent`, `delivered`, `read`, `failed` |
| `recipient` | string(180) | yes | | phone/email |
| `template_id` | string(120) | yes | | |
| `body_preview` | string(500) | yes | | |
| `sent_at` | timestamp | yes | | |
| `metadata` | json | yes | | provider response |

### 4.29 `crm_tasks`

Pipeline action menu includes Tasks.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | yes | | |
| `campaign_id` | foreignId(crm_campaigns) | yes | | |
| `assigned_to` | foreignId(users) | no | | |
| `created_by` | foreignId(users) | no | | |
| `title` | string(180) | no | | |
| `description` | text | yes | | |
| `status` | enum | no | `open` | `open`, `in_progress`, `completed`, `cancelled` |
| `due_at` | timestamp | yes | | |
| `completed_at` | timestamp | yes | | |

### 4.30 `crm_timeline_events`

Used by expanded lead summary timeline.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `lead_id` | foreignId(crm_leads) | no | | |
| `user_id` | foreignId(users) | yes | | |
| `event_type` | enum | no | | `lead_created`, `call_answered`, `call_missed`, `lead_disposed`, `followup_scheduled`, `followup_completed`, `lead_updated`, `lead_reassigned`, `message_sent` |
| `title` | string(180) | no | | |
| `description` | text | yes | | |
| `payload` | json | yes | | |
| `occurred_at` | timestamp | no | | indexed |

### 4.31 `crm_saved_filters`

For "Save Filter" buttons.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `user_id` | foreignId(users) | no | | |
| `module` | enum | no | | `leads`, `reports`, `campaigns`, `calls` |
| `name` | string(120) | no | | |
| `filters` | json | no | | |
| `is_default` | boolean | no | false | |

### 4.32 `crm_report_exports`

For async downloads.

| Field | Type | Null | Default | Notes |
|---|---:|:---:|---|---|
| `id` | bigIncrements | no | | PK |
| `user_id` | foreignId(users) | no | | requester |
| `report_type` | string(80) | no | | |
| `filters` | json | yes | | |
| `status` | enum | no | `queued` | `queued`, `processing`, `completed`, `failed` |
| `file_path` | string(500) | yes | | |
| `error_message` | text | yes | | |
| `expires_at` | timestamp | yes | | |

## 5. Model Design

### `CrmBusinessProfile`

Table: `crm_business_profiles`

Relationships: hasMany pipelines, contactProperties, leadPriorityRules, users through business scope if multi-tenant.

Validations: business name required max 160, phone required, address max 250, GST format optional, start time before end time.

Computed fields: `working_hours_label`, `full_address`.

### `User`

Table: `users`

Relationships: belongsTo reportingManager, hasMany reportees, belongsToMany roles, belongsToMany campaigns, hasMany leads as assignedLeads, hasMany callLogs, sessions, breaks, followUps.

Scopes: `activeCrm`, `agents`, `teamLeads`, `assignmentEnabled`, `reportingTo($managerId)`.

Computed fields: `role_label`, `is_online`, `current_break_duration`, `assigned_campaigns_count`.

### `CrmTeam`

Table: `crm_teams`

Relationships: belongsTo teamLead, belongsToMany users.

Scopes: `active`.

### `CrmPipeline`

Table: `crm_pipelines`

Relationships: hasMany stages, stageTags through stages, dispositions, campaigns, retryReasons.

Validations: name required unique per business, color hex.

Scopes: `active`, `ordered`.

Hooks: create default stages and priority tags when a new pipeline is created.

### `CrmLeadStage`

Table: `crm_lead_stages`

Relationships: belongsTo pipeline, hasMany tags, hasMany leads, hasMany dispositions.

Scopes: `open`, `closed`, `ordered`.

Computed fields: `lead_count`, `conversion_percent`.

### `CrmStageTag`

Table: `crm_stage_tags`

Relationships: belongsTo stage, hasMany leads, hasMany dispositions.

### `CrmCampaign`

Table: `crm_campaigns`

Relationships: belongsTo pipeline, belongsTo manager, belongsToMany users, hasMany leads, contactLists, callLogs, followUps.

Validations: name required max 160, distribution enum, priority enum, manager must have CRM user permission.

Scopes: `active`, `paused`, `pinned`, `byPipeline`, `visibleToUser($user)`.

Computed fields: `total_leads`, `assigned_leads`, `unassigned_leads`, `called_leads`, `closed_leads`, `rescheduled_leads`.

Hooks: when creating campaign, attach manager and selected agents; when status changes to paused, block auto assignment.

### `CrmContactList`

Table: `crm_contact_lists`

Relationships: belongsTo campaign, belongsTo uploadedBy, hasMany rows, hasMany leads.

Validations: file type csv/xls/xlsx, max size 3 MB, max rows 25,000.

Computed fields: `success_count`, `failure_rate`.

### `CrmContactListRow`

Table: `crm_contact_list_rows`

Relationships: belongsTo contactList, belongsTo lead.

Scopes: `created`, `merged`, `failed`.

### `CrmLead`

Table: `crm_leads`

Relationships: belongsTo campaign, pipeline, stage, tag, assignedUser, source, contactList; hasMany phoneNumbers, propertyValues, callLogs, dispositions, followUps, notes, timelineEvents, communicationEvents.

Validations: primary phone required and valid, email valid nullable, campaign required, assigned user must belong to campaign unless admin override.

Scopes: `assignedTo($userId)`, `unassigned`, `byCampaign`, `byStage`, `byStatus`, `dueFollowUp`, `searchPhone`, `priorityOrdered`.

Computed fields: `latest_remark`, `latest_call_status`, `is_followup_due`, `display_stage`, `display_tag`, `call_attempts_count`.

Hooks: normalize phone; update `last_call_at`, `next_follow_up_at`, `total_disposition_count`; write timeline event on create/update/reassign.

### `CrmContactProperty`

Table: `crm_contact_properties`

Relationships: hasMany leadPropertyValues.

Validations: name max 60, max 40 active properties, data type enum.

Hooks: create slug from name.

### `CrmLeadPropertyValue`

Table: `crm_lead_property_values`

Relationships: belongsTo lead, belongsTo property.

Validations: value must match property data type.

### `CrmDisposition`

Table: `crm_dispositions`

Relationships: belongsTo pipeline, stage, tag; hasMany leadDispositions.

Scopes: `active`, `forPipeline`, `requiresFollowUp`.

### `CrmCallLog`

Table: `crm_call_logs`

Relationships: belongsTo lead, campaign, user, disposition; hasOne leadDisposition.

Validations: lead/campaign/user required for outgoing calls, duration non-negative, status enum.

Scopes: `connected`, `notConnected`, `incoming`, `outgoing`, `dateRange`, `forUser`, `forCampaign`.

Computed fields: `duration_label`, `was_connected`.

Hooks: update lead last call and dashboard aggregates after call closes.

### `CrmLeadDisposition`

Table: `crm_lead_dispositions`

Relationships: belongsTo lead, callLog, user, campaign, disposition, fromStage, toStage, tag.

Hooks: update lead stage/tag/status, increment disposition count, schedule follow-up if required, write timeline.

### `CrmFollowUp`

Table: `crm_follow_ups`

Relationships: belongsTo lead, campaign, user, createdBy, callLog.

Scopes: `dueToday`, `missed`, `upcoming`, `forUser`.

Hooks: update lead `next_follow_up_at`; mark missed by scheduler when due time passes.

### `CrmRetryReason` and `CrmRetryRule`

Tables: `crm_retry_reasons`, `crm_retry_rules`

Relationships: reason hasOne rule.

Hooks: when not-connected call closes, create system follow-up based on retry rule; after exhausted retries mark lead lost if enabled.

### `CrmUserSession` and `CrmUserBreak`

Tables: `crm_user_sessions`, `crm_user_breaks`

Relationships: session belongsTo user, hasMany breaks.

Computed fields: `login_duration`, `total_break_duration`.

### `CrmCommunicationEvent`

Table: `crm_communication_events`

Relationships: belongsTo lead, campaign, user.

Scopes: `sms`, `email`, `whatsapp`, `sent`, `dateRange`.

### `CrmTask`

Table: `crm_tasks`

Relationships: belongsTo lead, campaign, assignedTo, createdBy.

Scopes: `open`, `due`, `assignedTo`.

### `CrmTimelineEvent`

Table: `crm_timeline_events`

Relationships: belongsTo lead, user.

Scopes: `latest`, `dateRange`, `type`.

## 6. Controller Routes And API Contracts

Base API path: `/api/admin/calling-crm`. All responses should be JSON. List endpoints use pagination: `page`, `per_page` default 25 max 100, `sort`, `direction`. Date filters accept `date_range=today|yesterday|last_7_days|last_30_days|this_month|custom` plus `from`, `to`.

Standard success response:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

Standard error response:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

### 6.1 Auth / Sessions

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `POST` | `/sessions/login-track` | create CRM login session | authenticated |
| `POST` | `/sessions/logout-track` | close active CRM session | authenticated |
| `POST` | `/sessions/breaks/start` | start break | authenticated |
| `POST` | `/sessions/breaks/{break}/end` | end break | authenticated |
| `GET` | `/sessions/current` | current online/break state | authenticated |

Request `POST /sessions/breaks/start`: `{ "reason": "Lunch" }`.

Response: active session and break object.

### 6.2 Dashboard

| Method | Endpoint | Query/body | Purpose | Permission |
|---|---|---|---|---|
| `GET` | `/dashboard/overview` | `date_range,from,to,pipeline_id,campaign_ids[]` | call overview, connected ratio | `crm.dashboard.view` |
| `GET` | `/dashboard/agent-activity` | `date_range,team_id` | active/on-break/total agents | `crm.dashboard.view` |
| `GET` | `/dashboard/leads-by-stage` | `pipeline_id,campaign_ids[]` | lead stage counts and percents | `crm.dashboard.view` |
| `GET` | `/dashboard/pinned-campaigns` | none | pinned campaigns | `crm.dashboard.view` |
| `POST` | `/dashboard/pinned-campaigns` | `{campaign_id}` | pin campaign | `crm.campaigns.manage` |
| `DELETE` | `/dashboard/pinned-campaigns/{campaign}` | none | unpin campaign | `crm.campaigns.manage` |

Dashboard overview response:

```json
{
  "total_calls": 7479,
  "connected_calls": 2598,
  "connected_percent": 34.74,
  "not_connected_calls": 4881
}
```

### 6.3 Users / Agents

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/users` | list users with filters/search | `crm.users.manage` |
| `POST` | `/users` | create one or many users | `crm.users.manage` |
| `GET` | `/users/{user}` | user details | `crm.users.manage` |
| `PUT` | `/users/{user}` | update user | `crm.users.manage` |
| `PATCH` | `/users/{user}/status` | activate/deactivate | `crm.users.manage` |
| `PATCH` | `/users/{user}/password` | change password | `crm.users.manage` |
| `PATCH` | `/users/{user}/lead-assignment` | enable/disable lead assignment | `crm.users.manage` |
| `DELETE` | `/users/{user}` | soft delete user | `crm.users.manage` |
| `GET` | `/users/{user}/campaigns` | campaign details modal | `crm.users.manage` |
| `GET` | `/users/{user}/reassign-campaigns` | campaigns available for reassignment | `crm.leads.assign` |

List query: `search`, `role`, `status`, `reporting_manager_id`, `sort=name`, `direction=asc`.

Create body:

```json
{
  "users": [
    {
      "name": "Agent Name",
      "phone": "9201977461",
      "password": "secret",
      "role": "executive",
      "email": "agent@example.com",
      "employee_id": "EMP001",
      "reporting_manager_id": 10,
      "expires_at": "2026-10-10"
    }
  ]
}
```

Status codes: `200`, `201`, `422`, `403`, `404`.

### 6.4 Teams

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/teams` | list teams | `crm.users.manage` |
| `POST` | `/teams` | create team | `crm.users.manage` |
| `PUT` | `/teams/{team}` | update team | `crm.users.manage` |
| `POST` | `/teams/{team}/members` | add members | `crm.users.manage` |
| `DELETE` | `/teams/{team}/members/{user}` | remove member | `crm.users.manage` |

### 6.5 Pipelines, Stages, Tags

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/pipelines` | list pipelines | `crm.pipelines.manage` or `crm.campaigns.view` |
| `POST` | `/pipelines` | create pipeline | `crm.pipelines.manage` |
| `GET` | `/pipelines/{pipeline}` | pipeline detail with stages | `crm.pipelines.manage` |
| `PUT` | `/pipelines/{pipeline}` | update name/color | `crm.pipelines.manage` |
| `DELETE` | `/pipelines/{pipeline}` | soft delete pipeline | `crm.pipelines.manage` |
| `POST` | `/pipelines/{pipeline}/stages` | add stage | `crm.pipelines.manage` |
| `PUT` | `/stages/{stage}` | update stage | `crm.pipelines.manage` |
| `POST` | `/pipelines/{pipeline}/stages/reorder` | reorder stages | `crm.pipelines.manage` |
| `POST` | `/stages/{stage}/tags` | add tag | `crm.pipelines.manage` |
| `DELETE` | `/stage-tags/{tag}` | delete tag | `crm.pipelines.manage` |

Pipeline body: `{ "name": "Indore Sales", "color": "#763abb" }`.

Stage body: `{ "name": "Fresh Leads", "category": "fresh", "tags": ["No Season", "Future Requirement"] }`.

### 6.6 Campaigns

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/campaigns` | list all campaigns grouped by pipeline | `crm.campaigns.view` |
| `POST` | `/campaigns` | create campaign | `crm.campaigns.manage` |
| `GET` | `/campaigns/{campaign}` | campaign details | `crm.campaigns.view` |
| `PUT` | `/campaigns/{campaign}` | update campaign | `crm.campaigns.manage` |
| `PATCH` | `/campaigns/{campaign}/status` | pause/resume/complete | `crm.campaigns.manage` |
| `PATCH` | `/campaigns/{campaign}/priority` | update priority | `crm.campaigns.manage` |
| `POST` | `/campaigns/{campaign}/agents` | attach agents | `crm.campaigns.manage` |
| `DELETE` | `/campaigns/{campaign}/agents/{user}` | remove agent | `crm.campaigns.manage` |
| `GET` | `/campaigns/{campaign}/summary` | lead summary stats | `crm.campaigns.view` |
| `GET` | `/campaigns/{campaign}/lead-funnel` | funnel counts | `crm.campaigns.view` |
| `GET` | `/campaigns/{campaign}/tags-summary` | tags chart data | `crm.campaigns.view` |

Create body:

```json
{
  "name": "Campaign Ritik Patel May 20, 13:47",
  "pipeline_id": 1,
  "manager_id": 21,
  "agent_ids": [11, 12, 13],
  "distribution": "on_demand",
  "priority": "medium",
  "lead_chunk_size": 10,
  "settings": {}
}
```

Campaign list query: `search`, `pipeline_id`, `status`, `hide_paused=true`.

### 6.7 Lead Imports / Contact Lists

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `POST` | `/campaigns/{campaign}/imports` | upload CSV/XLS/XLSX | `crm.imports.manage` |
| `GET` | `/campaigns/{campaign}/imports` | list uploaded files | `crm.imports.manage` |
| `GET` | `/imports/{import}` | import summary | `crm.imports.manage` |
| `GET` | `/imports/{import}/rows` | row details with status filter | `crm.imports.manage` |
| `GET` | `/imports/{import}/sample` | download sample file | `crm.imports.manage` |

Upload multipart body: `file`, optional `sheet_name`, optional `mapping`.

Rows query: `status=created|merged|merged_reopened|failed`, `search`, pagination.

### 6.8 Leads

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/leads` | lead list/search | `crm.leads.view` |
| `POST` | `/leads` | add lead | `crm.leads.create` |
| `GET` | `/leads/{lead}` | lead details | `crm.leads.view` |
| `PUT` | `/leads/{lead}` | edit lead | `crm.leads.update` |
| `DELETE` | `/leads/{lead}` | soft delete | `crm.leads.delete` |
| `GET` | `/leads/{lead}/timeline` | expanded timeline | `crm.leads.view` |
| `GET` | `/leads/{lead}/history` | dispose history | `crm.leads.view` |
| `POST` | `/leads/{lead}/notes` | add note | `crm.leads.update` |
| `POST` | `/leads/{lead}/phone-numbers` | add alternate number | `crm.leads.update` |
| `POST` | `/leads/bulk/update` | bulk update selected leads | `crm.leads.bulk_actions` |
| `POST` | `/leads/bulk/delete` | bulk delete | `crm.leads.bulk_actions` |
| `POST` | `/leads/bulk/move` | move to another campaign | `crm.leads.bulk_actions` |
| `POST` | `/leads/bulk/copy` | copy to another campaign | `crm.leads.bulk_actions` |
| `POST` | `/leads/bulk/close` | close leads | `crm.leads.bulk_actions` |
| `POST` | `/leads/reassign` | reassign leads/users/campaigns | `crm.leads.assign` |
| `POST` | `/leads/claim-next` | on-demand assignment chunk | `crm.calls.start` |

Lead list query:

`search`, `phone`, `campaign_id`, `pipeline_id`, `stage_id`, `tag_id`, `assigned_user_id`, `status`, `source_id`, `creation_date`, `updated_date`, `follow_up_from`, `follow_up_to`, `last_call_from`, `last_call_to`, custom property filters as `properties[property_slug]=value`.

Create body:

```json
{
  "campaign_id": 1,
  "name": "Jaiswal Gopal Traders",
  "primary_phone": "7047246623",
  "email": null,
  "assigned_user_id": 11,
  "stage_id": 1,
  "tag_id": null,
  "source_code": "MANUAL",
  "properties": {
    "company_name": "Jaiswal Gopal Traders",
    "town_city": "Mandla",
    "state": "Madhya Pradesh",
    "gst": null
  }
}
```

Lead response should include table-ready fields: creation date, updated date, stage, tag, user assigned, follow-up time, lead status, last call date, total disposition count, deal amount, custom properties, latest remark, timeline.

### 6.9 Lead Assignment / Reassignment

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `POST` | `/leads/reassign` | selected leads to another user | `crm.leads.assign` |
| `POST` | `/campaigns/{campaign}/reassign-all` | reassign all leads in campaign | `crm.leads.assign` |
| `POST` | `/users/{user}/reassign-leads` | reassign user's leads | `crm.leads.assign` |
| `GET` | `/users/{user}/reassign-summary` | UI summary report | `crm.leads.assign` |

Body:

```json
{
  "lead_ids": [1, 2, 3],
  "from_user_id": 11,
  "to_user_id": 12,
  "to_campaign_id": 2,
  "reason": "Agent inactive"
}
```

### 6.10 Calls / Auto Dialing

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `POST` | `/calls/start` | start call for lead | `crm.calls.start` |
| `POST` | `/calls/webhook` | telephony provider callback | signed provider webhook |
| `PATCH` | `/calls/{call}` | update call status/duration | provider/admin |
| `GET` | `/calls` | list call logs | `crm.calls.view` |
| `GET` | `/calls/{call}` | call detail | `crm.calls.view` |
| `GET` | `/campaigns/{campaign}/call-logs` | campaign call logs | `crm.calls.view` |
| `GET` | `/users/{user}/call-logs` | user call logs | `crm.calls.view` |

Start body:

```json
{
  "lead_id": 1001,
  "phone_number": "7876375446",
  "mode": "manual"
}
```

Webhook body should store provider call ID, status, timestamps, duration, recording URL.

### 6.11 Dispositions

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/pipelines/{pipeline}/dispositions` | list dispositions | `crm.dispositions.manage` |
| `POST` | `/pipelines/{pipeline}/dispositions` | create disposition | `crm.dispositions.manage` |
| `PUT` | `/dispositions/{disposition}` | update disposition | `crm.dispositions.manage` |
| `DELETE` | `/dispositions/{disposition}` | delete disposition | `crm.dispositions.manage` |
| `POST` | `/leads/{lead}/dispose` | dispose lead after call | `crm.calls.start` |

Dispose body:

```json
{
  "call_log_id": 55,
  "disposition_id": 4,
  "stage_id": 2,
  "tag_id": 8,
  "call_status": "connected",
  "remark": "Customer asked for price list",
  "follow_up_at": "2026-05-20T14:55:00+05:30",
  "deal_amount": 25000
}
```

Status codes: `201`, `422` when required follow-up/note is missing.

### 6.12 Follow-Ups

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/follow-ups` | list due/missed/upcoming follow-ups | `crm.followups.manage` |
| `POST` | `/follow-ups` | schedule follow-up | `crm.followups.manage` |
| `PATCH` | `/follow-ups/{followUp}` | reschedule/update | `crm.followups.manage` |
| `PATCH` | `/follow-ups/{followUp}/complete` | complete follow-up | `crm.followups.manage` |
| `DELETE` | `/follow-ups/{followUp}` | cancel follow-up | `crm.followups.manage` |

Query: `user_id`, `campaign_id`, `status`, `from`, `to`.

### 6.13 Custom Contact Properties

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/contact-properties` | list properties | `crm.settings.manage` |
| `POST` | `/contact-properties` | create property | `crm.settings.manage` |
| `PUT` | `/contact-properties/{property}` | update property | `crm.settings.manage` |
| `PATCH` | `/contact-properties/{property}/toggle` | activate/deactivate | `crm.settings.manage` |
| `DELETE` | `/contact-properties/{property}` | delete property | `crm.settings.manage` |

Body:

```json
{
  "name": "Company Name",
  "data_type": "text",
  "options": null,
  "is_required": false,
  "sort_order": 1
}
```

Validation: max 40 active custom properties, name max 60.

### 6.14 Retry Settings

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/retry-reasons` | list retry reasons and rules | `crm.settings.manage` |
| `POST` | `/retry-reasons` | create reason | `crm.settings.manage` |
| `PUT` | `/retry-reasons/{reason}` | rename reason | `crm.settings.manage` |
| `DELETE` | `/retry-reasons/{reason}` | delete reason | `crm.settings.manage` |
| `PUT` | `/retry-reasons/{reason}/rule` | update retry logic | `crm.settings.manage` |
| `PATCH` | `/retry-reasons/{reason}/toggle` | enable/disable | `crm.settings.manage` |

Rule body:

```json
{
  "logic_type": "fixed",
  "max_retries": 5,
  "interval_value": 1,
  "interval_unit": "hours",
  "mark_lost_after_exhausted": true,
  "apply_to_all": false
}
```

### 6.15 Lead Priority

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/lead-priority-rules` | list priority order | `crm.settings.manage` |
| `POST` | `/lead-priority-rules/reorder` | save drag order | `crm.settings.manage` |

Body:

```json
{
  "rules": [
    {"code": "manual_scheduled", "sort_order": 1},
    {"code": "assigned_uncontacted", "sort_order": 2}
  ]
}
```

### 6.16 Reports

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/reports/catalog` | report list | `crm.reports.view` |
| `GET` | `/reports/user-call` | user call report table | `crm.reports.view` |
| `GET` | `/reports/lead-disposition` | disposition report | `crm.reports.view` |
| `GET` | `/reports/follow-ups` | missed/due follow-ups | `crm.reports.view` |
| `GET` | `/reports/campaign` | campaign report | `crm.reports.view` |
| `GET` | `/reports/login` | login report | `crm.reports.view` |
| `GET` | `/reports/hourly` | hourly report | `crm.reports.view` |
| `GET` | `/reports/day` | day report | `crm.reports.view` |
| `POST` | `/reports/exports` | queue export | `crm.reports.export` |
| `GET` | `/reports/exports/{export}` | export status/download URL | `crm.reports.export` |

Common report query: `date_range`, `from`, `to`, `user_ids[]`, `campaign_ids[]`, `pipeline_id`, `team_id`, `page`, `per_page`, `sort`.

User call report columns:

- user name, reporting manager, mobile number, date
- total calls, total connected, total unconnected
- outgoing total/connected/unanswered, average outgoing duration
- incoming total/connected/unanswered, average incoming duration
- total disposed count, disposed connected count, disposed not connected count
- in-progress leads, converted leads, lost leads, follow-ups due today
- average start calling time, average call duration, average form filling time
- total call duration, number of breaks, break duration
- WhatsApp, email, SMS sent

### 6.17 Trends / Analytics

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/trends/widgets` | summary widgets | `crm.reports.view` |
| `GET` | `/trends/calls-vs-connected` | grouped chart | `crm.reports.view` |
| `GET` | `/trends/call-duration` | duration trend | `crm.reports.view` |
| `GET` | `/trends/conversion-ratio` | conversion trend | `crm.reports.view` |
| `GET` | `/trends/leads-added` | lead creation trend | `crm.reports.view` |
| `GET` | `/trends/lead-sources` | source chart | `crm.reports.view` |
| `GET` | `/trends/lost-leads` | lost leads chart | `crm.reports.view` |

Widget response fields:

`total_sms_sent`, `total_calls`, `total_converted_leads`, `total_call_time_seconds`, `total_calls_connected`, `total_lost_leads`, each with comparison percent and direction.

### 6.18 Saved Filters

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `GET` | `/saved-filters` | list saved filters by module | authenticated |
| `POST` | `/saved-filters` | save filter | authenticated |
| `PUT` | `/saved-filters/{filter}` | update filter | owner/admin |
| `DELETE` | `/saved-filters/{filter}` | delete filter | owner/admin |

### 6.19 Communications

| Method | Endpoint | Purpose | Permission |
|---|---|---|---|
| `POST` | `/communications/sms` | send SMS | `crm.leads.update` |
| `POST` | `/communications/email` | send email | `crm.leads.update` |
| `POST` | `/communications/whatsapp` | send WhatsApp | `crm.leads.update` |
| `GET` | `/communications` | communication event list | `crm.reports.view` |

## 7. Filtering, Sorting, And Pagination Standards

All list endpoints should accept:

- `page`: integer, default 1
- `per_page`: integer, default 25, max 100
- `sort`: allowed column or computed sort key
- `direction`: `asc` or `desc`
- `search`: general free-text search

Date filtering:

- `date_range=today`
- `date_range=yesterday`
- `date_range=last_7_days`
- `date_range=last_30_days`
- `date_range=this_month`
- `date_range=custom&from=YYYY-MM-DD&to=YYYY-MM-DD`

Pagination response:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 245,
    "last_page": 10
  }
}
```

## 8. Status And Enum Reference

### Campaign Status

`draft`, `active`, `paused`, `completed`, `archived`

### Campaign Distribution

`on_demand`, `equal`, `conditional`, `auto_assign`

### Campaign Priority

`low`, `medium`, `high`, `critical`

### Lead Status

`uncontacted`, `in_progress`, `converted`, `lost`, `closed`, `reopened`

### Lead Source

`FILE_UPLOAD`, `WALK_IN_LEAD`, `INCOMING_IVR`, `WORKFLOW`, `GOOGLE_SHEET`, `MANUAL`, `API`, `WEBHOOK`

### Call Direction

`incoming`, `outgoing`

### Call Status

`initiated`, `ringing`, `connected`, `answered`, `not_connected`, `busy`, `no_answer`, `failed`, `missed`

### Follow-Up Status

`scheduled`, `due`, `completed`, `missed`, `cancelled`, `rescheduled`

### Import Row Status

`created`, `merged`, `merged_reopened`, `failed`, `skipped`

### Disposition Type

`fresh`, `in_progress`, `closed_won`, `closed_lost`, `not_connected`

### Communication Channel

`sms`, `email`, `whatsapp`

## 9. Background Jobs And Scheduled Tasks

| Job | Trigger | Purpose |
|---|---|---|
| `ProcessCrmContactListImport` | after upload | parse file, validate rows, create/merge leads |
| `SyncTelephonyCallStatus` | provider callback or scheduler | finalize call logs |
| `ApplyRetryRuleToCall` | not-connected call completed | schedule retry follow-up |
| `MarkMissedFollowUps` | every 5 minutes | mark overdue scheduled follow-ups as missed |
| `RefreshCrmAnalyticsCache` | hourly/daily | cache dashboard and trend data |
| `GenerateCrmReportExport` | export request | produce CSV/XLSX |
| `ExpireCrmReportExports` | daily | delete expired export files |
| `CloseStaleUserSessions` | hourly | close stale online sessions |

## 10. Security And Validation Notes

- Never expose call recordings to unauthorized users; use signed URLs.
- Enforce team/manager scoping for team leads and executives.
- Validate uploaded files for extension, MIME, file size, row count, and formula injection before CSV export.
- Normalize phone numbers and store original raw value in import row payload.
- Use soft deletes for leads, campaigns, pipelines, properties, users, and roles.
- Bulk actions must be auditable through timeline events.
- Report exports should be queued, permission checked at download time, and expired automatically.
- Telephony webhooks must be signed or IP allowlisted.

## 11. Implementation Priority

1. Normalize existing migration names to `crm_*` or keep current names consistently. Avoid mixing `pipelines` and `crm_pipelines` unless intentionally integrating with existing tables.
2. Implement RBAC and user management.
3. Implement pipelines, stages, tags, campaigns, and campaign-agent assignments.
4. Implement leads, contact properties, imports, and lead summary APIs.
5. Implement call logs, dispositions, follow-ups, retry rules, and timeline.
6. Implement dashboard widgets and report endpoints.
7. Implement export jobs and telephony/messaging provider integrations.
