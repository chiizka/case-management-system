<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property \Illuminate\Support\Carbon|null $date_returned_case_mgmt
 * @property string|null $review_ct_cnpc
 * @property \Illuminate\Support\Carbon|null $date_received_drafter_finalization_2nd
 * @property \Illuminate\Support\Carbon|null $date_returned_case_mgmt_signature_2nd
 * @property \Illuminate\Support\Carbon|null $date_order_2nd_cnpc
 * @property \Illuminate\Support\Carbon|null $released_date_2nd_cnpc
 * @property \Illuminate\Support\Carbon|null $date_forwarded_malsu
 * @property \Illuminate\Support\Carbon|null $motion_reconsideration_date
 * @property \Illuminate\Support\Carbon|null $date_received_malsu
 * @property \Illuminate\Support\Carbon|null $date_resolution_mr
 * @property \Illuminate\Support\Carbon|null $released_date_resolution_mr
 * @property \Illuminate\Support\Carbon|null $date_appeal_received_records
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateAppealReceivedRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateForwardedMalsu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateOrder2ndCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReceivedDrafterFinalization2nd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReceivedMalsu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateResolutionMr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReturnedCaseMgmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReturnedCaseMgmtSignature2nd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereMotionReconsiderationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReleasedDate2ndCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReleasedDateResolutionMr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReviewCtCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class AppealsAndResolution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $inspection_id
 * @property string|null $case_no
 * @property string $establishment_name
 * @property string $current_stage
 * @property string $overall_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AppealsAndResolution> $appealsAndResolutions
 * @property-read int|null $appeals_and_resolutions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComplianceAndAward> $complianceAndAwards
 * @property-read int|null $compliance_and_awards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\docketing> $docketing
 * @property-read int|null $docketing_count
 * @property-read \App\Models\DocumentTracking|null $documentTracking
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HearingProcess> $hearingProcesses
 * @property-read int|null $hearing_processes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inspection> $inspections
 * @property-read int|null $inspections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderAndDisposition> $ordersAndDisposition
 * @property-read int|null $orders_and_disposition_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReviewAndDrafting> $reviewAndDrafting
 * @property-read int|null $review_and_drafting_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCurrentStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereEstablishmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereInspectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereOverallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class CaseFile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property string|null $compliance_order_monetary_award
 * @property string|null $osh_penalty
 * @property int|null $affected_male
 * @property int|null $affected_female
 * @property int $first_order_dismissal_cnpc
 * @property int $tavable_less_than_10_workers
 * @property int $with_deposited_monetary_claims
 * @property string|null $amount_deposited
 * @property int $with_order_payment_notice
 * @property string|null $status_all_employees_received
 * @property string|null $status_case_after_first_order
 * @property string|null $date_notice_finality_dismissed
 * @property string|null $released_date_notice_finality
 * @property int $updated_ticked_in_mis
 * @property string|null $second_order_drafter
 * @property string|null $date_received_by_drafter_ct_cnpc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAffectedFemale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAffectedMale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAmountDeposited($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereComplianceOrderMonetaryAward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereDateNoticeFinalityDismissed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereDateReceivedByDrafterCtCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereFirstOrderDismissalCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereOshPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereReleasedDateNoticeFinality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereSecondOrderDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereStatusAllEmployeesReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereStatusCaseAfterFirstOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereTavableLessThan10Workers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereUpdatedTickedInMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereWithDepositedMonetaryClaims($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereWithOrderPaymentNotice($value)
 * @mixin \Eloquent
 */
	class ComplianceAndAward extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property string $current_role
 * @property string $status
 * @property int|null $transferred_by_user_id
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $received_by_user_id
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property string|null $transfer_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentTrackingHistory> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\User|null $receivedBy
 * @property-read \App\Models\User|null $transferredBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCurrentRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereReceivedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferredByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DocumentTracking extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $document_tracking_id
 * @property string|null $from_role
 * @property string $to_role
 * @property int|null $transferred_by_user_id
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $received_by_user_id
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DocumentTracking $documentTracking
 * @property-read \App\Models\User|null $receivedBy
 * @property-read \App\Models\User|null $transferredBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereDocumentTrackingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereFromRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereReceivedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereToRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereTransferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereTransferredByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DocumentTrackingHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property \Illuminate\Support\Carbon|null $date_1st_mc_actual
 * @property string|null $first_mc_pct
 * @property string|null $status_1st_mc
 * @property \Illuminate\Support\Carbon|null $date_2nd_last_mc
 * @property string|null $second_last_mc_pct
 * @property string|null $status_2nd_mc
 * @property string|null $case_folder_forwarded_to_ro
 * @property string|null $complete_case_folder
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCaseFolderForwardedToRo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCompleteCaseFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereDate1stMcActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereDate2ndLastMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereFirstMcPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereSecondLastMcPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereStatus1stMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereStatus2ndMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class HearingProcess extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property string|null $po_office
 * @property string|null $inspector_name
 * @property string|null $inspector_authority_no
 * @property string|null $date_of_inspection
 * @property string|null $date_of_nr
 * @property string|null $twg_ali
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $lapse_20_day_period
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @property mixed $lapse20_day_period
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereDateOfInspection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereDateOfNr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereInspectorAuthorityNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereInspectorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereLapse20DayPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection wherePoOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereTwgAli($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Inspection extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $activity
 * @property string|null $action
 * @property string|null $resource_type
 * @property string|null $resource_id
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereResourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereResourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserId($value)
 * @mixin \Eloquent
 */
	class Log extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property int|null $aging_2_days_finalization Calculated field
 * @property string|null $status_finalization
 * @property int|null $pct_96_days Calculated field
 * @property \Illuminate\Support\Carbon|null $date_signed_mis
 * @property string|null $status_pct
 * @property \Illuminate\Support\Carbon|null $reference_date_pct
 * @property int|null $aging_pct Calculated field
 * @property string|null $disposition_mis
 * @property string|null $disposition_actual
 * @property string|null $findings_to_comply
 * @property \Illuminate\Support\Carbon|null $date_of_order_actual
 * @property \Illuminate\Support\Carbon|null $released_date_actual
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereAging2DaysFinalization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereAgingPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDateOfOrderActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDateSignedMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDispositionActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDispositionMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereFindingsToComply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition wherePct96Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereReferenceDatePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereReleasedDateActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereStatusFinalization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereStatusPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class OrderAndDisposition extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property string|null $draft_order_type
 * @property string $applicable_draft_order
 * @property int|null $po_pct
 * @property int|null $aging_po_pct
 * @property string $status_po_pct
 * @property \Illuminate\Support\Carbon|null $date_received_from_po
 * @property string|null $reviewer_drafter
 * @property \Illuminate\Support\Carbon|null $date_received_by_reviewer
 * @property \Illuminate\Support\Carbon|null $date_returned_from_drafter
 * @property int|null $aging_10_days_tssd
 * @property string $status_reviewer_drafter
 * @property string|null $draft_order_tssd_reviewer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereAging10DaysTssd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereAgingPoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereApplicableDraftOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReceivedByReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReceivedFromPo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReturnedFromDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDraftOrderTssdReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDraftOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting wherePoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereReviewerDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereStatusPoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereStatusReviewerDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ReviewAndDrafting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $password_reset_sent_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $otp_code
 * @property \Illuminate\Support\Carbon|null $otp_expires_at
 * @property bool $two_factor_enabled
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtpCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtpExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordResetSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $case_id
 * @property string|null $pct_for_docketing
 * @property string|null $date_scheduled_docketed
 * @property int|null $aging_docket
 * @property string|null $status_docket
 * @property string|null $hearing_officer_mis
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereAgingDocket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereDateScheduledDocketed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereHearingOfficerMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing wherePctForDocketing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereStatusDocket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class docketing extends \Eloquent {}
}

