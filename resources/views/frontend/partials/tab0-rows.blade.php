@foreach($cases as $case)
    <tr data-id="{{ $case->id }}">
        <td class="actions-cell collapsed">
            <div class="action-buttons-container">
                <button class="action-toggle-btn" type="button">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="action-buttons">
                    <button class="btn btn-warning btn-sm edit-row-btn-case"
                            data-case-id="{{ $case->id }}"
                            title="Edit Row">
                        <i class="fas fa-edit"></i>
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-sm delete-btn"
                            data-case-id="{{ $case->id }}"
                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>

                    <button class="btn btn-info btn-sm view-history-btn"
                            data-case-id="{{ $case->id }}"
                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                            title="View Document History">
                        <i class="fas fa-history"></i>
                    </button>

                    <button type="button"
                            class="btn btn-primary btn-sm document-checklist-btn"
                            data-case-id="{{ $case->id }}"
                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                            title="Document Checklist">
                        <i class="fas fa-file-alt"></i>
                    </button>

                    @if(Auth::user()->isProvince())
                        <button type="button"
                                class="btn btn-warning btn-sm dispose-case-btn"
                                data-case-id="{{ $case->id }}"
                                data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                title="Mark as Disposed">
                            <i class="fas fa-archive"></i>
                        </button>
                    @else
                        <button type="button"
                                class="btn btn-success btn-sm complete-case-btn"
                                data-case-id="{{ $case->id }}"
                                data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                title="Mark as Complete">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    @endif
                </div>
            </div>
        </td>

        {{-- Core Information --}}
        <td class="editable-cell" data-field="no">{{ $case->no ?? '-' }}</td>
        <td class="editable-cell" data-field="inspection_id">{{ $case->inspection_id ?? '-' }}</td>
        <td class="editable-cell" data-field="case_no">{{ $case->case_no ?? '-' }}</td>
        <td class="editable-cell wrap-cell" data-field="establishment_name">{{ $case->establishment_name ?? '-' }}</td>
        {{-- <td class="editable-cell wrap-cell" data-field="establishment_address">{{ $case->establishment_address ?? '-' }}</td> --}}
        <td class="editable-cell" data-field="mode">{{ $case->mode ?? '-' }}</td>
        <td class="readonly-cell" data-field="po_office">{{ $case->po_office ?? '-' }}</td>
        <td class="editable-cell" data-field="type_of_industry">{{ $case->type_of_industry ?? '-' }}</td>
        {{-- <td class="readonly-cell" data-field="overall_status">{{ $case->overall_status ?? '-' }}</td> --}}

        {{-- Inspection Stage --}}
        <td class="editable-cell" data-field="date_of_inspection" data-type="date">
            {{ $case->date_of_inspection ? \Carbon\Carbon::parse($case->date_of_inspection)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="inspector_name" title="{{ $case->inspector_name ?? '' }}">
            {{ $case->inspector_name ? Str::limit($case->inspector_name, 20) : '-' }}
        </td>
        <td class="editable-cell" data-field="inspector_authority_no">{{ $case->inspector_authority_no ?? '-' }}</td>
        <td class="editable-cell" data-field="date_of_nr" data-type="date">
            {{ $case->date_of_nr ? \Carbon\Carbon::parse($case->date_of_nr)->format('Y-m-d') : '-' }}
        </td>
        {{-- <td class="readonly-cell" data-field="lapse_20_day_period">
            {{ $case->lapse_20_day_period ? $case->lapse_20_day_period->format('Y-m-d') : '-' }}
        </td> --}}

        {{-- Docketing Stage --}}
        {{-- <td class="readonly-cell" data-field="pct_for_docketing">
            {{ $case->pct_for_docketing ? $case->pct_for_docketing->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">
            {{ $case->date_scheduled_docketed ? \Carbon\Carbon::parse($case->date_scheduled_docketed)->format('Y-m-d') : '-' }}
        </td>
        <td class="readonly-cell" data-field="aging_docket">{{ $case->aging_docket ?? '-' }}</td>
        <td class="readonly-cell" data-field="status_docket">{{ $case->status_docket ?? '-' }}</td> --}}
        <td class="editable-cell" data-field="hearing_officer_mis" title="{{ $case->hearing_officer_mis ?? '' }}">
            {{ $case->hearing_officer_mis ? Str::limit($case->hearing_officer_mis, 20) : '-' }}
        </td>

        {{-- Hearing Process Stage --}}
        {{-- <td class="editable-cell" data-field="date_1st_mc_actual" data-type="date">
            {{ $case->date_1st_mc_actual ? \Carbon\Carbon::parse($case->date_1st_mc_actual)->format('Y-m-d') : '-' }}
        </td>
        <td class="readonly-cell" data-field="first_mc_pct">{{ $case->first_mc_pct ?? '-' }}</td>
        <td class="readonly-cell" data-field="status_1st_mc">{{ $case->status_1st_mc ?? '-' }}</td>
        <td class="editable-cell" data-field="date_2nd_last_mc" data-type="date">
            {{ $case->date_2nd_last_mc ? \Carbon\Carbon::parse($case->date_2nd_last_mc)->format('Y-m-d') : '-' }}
        </td>
        <td class="readonly-cell" data-field="second_last_mc_pct">{{ $case->second_last_mc_pct ?? '-' }}</td>
        <td class="readonly-cell" data-field="status_2nd_mc">{{ $case->status_2nd_mc ?? '-' }}</td>
        <td class="editable-cell" data-field="case_folder_forwarded_to_ro" data-type="date">
            {{ $case->case_folder_forwarded_to_ro ? \Carbon\Carbon::parse($case->case_folder_forwarded_to_ro)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="draft_order_from_po_type">{{ $case->draft_order_from_po_type ?? '-' }}</td>
        <td class="editable-cell" data-field="applicable_draft_order">{{ $case->applicable_draft_order ?? '-' }}</td>
        <td class="editable-cell" data-field="complete_case_folder">{{ $case->complete_case_folder ?? '-' }}</td>
        <td class="editable-cell" data-field="twg_ali">{{ $case->twg_ali ?? '-' }}</td> --}}

        {{-- Review & Drafting Stage --}}
        {{-- <td class="readonly-cell" data-field="po_pct">
            {{ $case->po_pct ? $case->po_pct->format('Y-m-d') : '-' }}
        </td>
        <td class="readonly-cell" data-field="aging_po_pct">{{ $case->aging_po_pct ?? '-' }}</td>
        <td class="readonly-cell" data-field="status_po_pct">{{ $case->status_po_pct ?? '-' }}</td>
        <td class="editable-cell" data-field="date_received_from_po" data-type="date">
            {{ $case->date_received_from_po ? \Carbon\Carbon::parse($case->date_received_from_po)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="reviewer_drafter" title="{{ $case->reviewer_drafter ?? '' }}">
            {{ $case->reviewer_drafter ? Str::limit($case->reviewer_drafter, 20) : '-' }}
        </td>
        <td class="editable-cell" data-field="date_received_by_reviewer" data-type="date">
            {{ $case->date_received_by_reviewer ? \Carbon\Carbon::parse($case->date_received_by_reviewer)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_returned_from_drafter" data-type="date">
            {{ $case->date_returned_from_drafter ? \Carbon\Carbon::parse($case->date_returned_from_drafter)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="aging_10_days_tssd">{{ $case->aging_10_days_tssd ?? '-' }}</td>
        <td class="editable-cell" data-field="status_reviewer_drafter">{{ $case->status_reviewer_drafter ?? '-' }}</td>
        <td class="editable-cell" data-field="draft_order_tssd_reviewer">{{ $case->draft_order_tssd_reviewer ?? '-' }}</td>
        <td class="editable-cell" data-field="final_review_date_received" data-type="date">
            {{ $case->final_review_date_received ? \Carbon\Carbon::parse($case->final_review_date_received)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_received_drafter_finalization" data-type="date">
            {{ $case->date_received_drafter_finalization ? \Carbon\Carbon::parse($case->date_received_drafter_finalization)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_returned_case_mgmt_signature" data-type="date">
            {{ $case->date_returned_case_mgmt_signature ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="aging_2_days_finalization">{{ $case->aging_2_days_finalization ?? '-' }}</td>
        <td class="editable-cell" data-field="status_finalization">{{ $case->status_finalization ?? '-' }}</td> --}}

        {{-- Orders & Disposition Stage --}}
        <td class="editable-cell" data-field="pct_96_days">
            {{ $case->pct_96_days ? $case->pct_96_days->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" 
            data-field="status_po_pct" 
            data-type="select">
            {{ $case->status_po_pct ?? '-' }}
        </td>
        <td class="editable-cell" data-field="date_signed_mis" data-type="date">
            {{ $case->date_signed_mis ? \Carbon\Carbon::parse($case->date_signed_mis)->format('Y-m-d') : '-' }}
        </td>
        {{-- <td class="editable-cell" data-field="reference_date_pct" data-type="date">
            {{ $case->reference_date_pct ? \Carbon\Carbon::parse($case->reference_date_pct)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="aging_pct">{{ $case->aging_pct ?? '-' }}</td>
        <td class="editable-cell" data-field="disposition_mis">{{ $case->disposition_mis ?? '-' }}</td>
        <td class="editable-cell" data-field="disposition_actual">{{ $case->disposition_actual ?? '-' }}</td>
        <td class="editable-cell" data-field="findings_to_comply" title="{{ $case->findings_to_comply ?? '' }}">
            {{ $case->findings_to_comply ? Str::limit($case->findings_to_comply, 20) : '-' }}
        </td>
        <td class="editable-cell" data-field="compliance_order_monetary_award">
            {{ $case->compliance_order_monetary_award ? number_format($case->compliance_order_monetary_award, 2) : '-' }}
        </td>
        <td class="editable-cell" data-field="osh_penalty">
            {{ $case->osh_penalty ? number_format($case->osh_penalty, 2) : '-' }}
        </td>
        <td class="editable-cell" data-field="affected_male">{{ $case->affected_male ?? '-' }}</td>
        <td class="editable-cell" data-field="affected_female">{{ $case->affected_female ?? '-' }}</td>
        <td class="editable-cell" data-field="date_of_order_actual" data-type="date">
            {{ $case->date_of_order_actual ? \Carbon\Carbon::parse($case->date_of_order_actual)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="released_date_actual" data-type="date">
            {{ $case->released_date_actual ? \Carbon\Carbon::parse($case->released_date_actual)->format('Y-m-d') : '-' }}
        </td> --}}

        {{-- Compliance & Awards Stage --}}
        {{-- <td class="editable-cell" data-field="first_order_dismissal_cnpc" data-type="boolean">
            {{ $case->first_order_dismissal_cnpc ? 'Yes' : 'No' }}
        </td>
        <td class="editable-cell" data-field="tavable_less_than_10_workers" data-type="boolean">
            {{ $case->tavable_less_than_10_workers ? 'Yes' : 'No' }}
        </td>
        <td class="editable-cell" data-field="scanned_order_first">{{ $case->scanned_order_first ?? '-' }}</td>
        <td class="editable-cell" data-field="with_deposited_monetary_claims" data-type="boolean">
            {{ $case->with_deposited_monetary_claims ? 'Yes' : 'No' }}
        </td>
        <td class="editable-cell" data-field="amount_deposited">
            {{ $case->amount_deposited ? number_format($case->amount_deposited, 2) : '-' }}
        </td>
        <td class="editable-cell" data-field="with_order_payment_notice" data-type="boolean">
            {{ $case->with_order_payment_notice ? 'Yes' : 'No' }}
        </td>
        <td class="editable-cell" data-field="status_all_employees_received">{{ $case->status_all_employees_received ?? '-' }}</td>
        <td class="editable-cell" data-field="status_case_after_first_order">{{ $case->status_case_after_first_order ?? '-' }}</td>
        <td class="editable-cell" data-field="date_notice_finality_dismissed" data-type="date">
            {{ $case->date_notice_finality_dismissed ? \Carbon\Carbon::parse($case->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="released_date_notice_finality" data-type="date">
            {{ $case->released_date_notice_finality ? \Carbon\Carbon::parse($case->released_date_notice_finality)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="scanned_notice_finality">{{ $case->scanned_notice_finality ?? '-' }}</td>
        <td class="editable-cell" data-field="updated_ticked_in_mis" data-type="boolean">
            {{ $case->updated_ticked_in_mis ? 'Yes' : 'No' }}
        </td> --}}

        {{-- Appeals & Resolution Stage (2nd Order) --}}
        {{-- <td class="editable-cell" data-field="second_order_drafter" title="{{ $case->second_order_drafter ?? '' }}">
            {{ $case->second_order_drafter ? Str::limit($case->second_order_drafter, 20) : '-' }}
        </td>
        <td class="editable-cell" data-field="date_received_by_drafter_ct_cnpc" data-type="date">
            {{ $case->date_received_by_drafter_ct_cnpc ? \Carbon\Carbon::parse($case->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_returned_case_mgmt_ct_cnpc" data-type="date">
            {{ $case->date_returned_case_mgmt_ct_cnpc ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_ct_cnpc)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="review_ct_cnpc">{{ $case->review_ct_cnpc ?? '-' }}</td>
        <td class="editable-cell" data-field="date_received_drafter_finalization_2nd" data-type="date">
            {{ $case->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($case->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_returned_case_mgmt_signature_2nd" data-type="date">
            {{ $case->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_order_2nd_cnpc" data-type="date">
            {{ $case->date_order_2nd_cnpc ? \Carbon\Carbon::parse($case->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="released_date_2nd_cnpc" data-type="date">
            {{ $case->released_date_2nd_cnpc ? \Carbon\Carbon::parse($case->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="scanned_order_2nd_cnpc">{{ $case->scanned_order_2nd_cnpc ?? '-' }}</td> --}}

        {{-- Appeals & Resolution Stage (MALSU) --}}
        {{-- <td class="editable-cell" data-field="date_forwarded_malsu" data-type="date">
            {{ $case->date_forwarded_malsu ? \Carbon\Carbon::parse($case->date_forwarded_malsu)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="scanned_indorsement_malsu">{{ $case->scanned_indorsement_malsu ?? '-' }}</td>
        <td class="editable-cell" data-field="motion_reconsideration_date" data-type="date">
            {{ $case->motion_reconsideration_date ? \Carbon\Carbon::parse($case->motion_reconsideration_date)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_received_malsu" data-type="date">
            {{ $case->date_received_malsu ? \Carbon\Carbon::parse($case->date_received_malsu)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_resolution_mr" data-type="date">
            {{ $case->date_resolution_mr ? \Carbon\Carbon::parse($case->date_resolution_mr)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="released_date_resolution_mr" data-type="date">
            {{ $case->released_date_resolution_mr ? \Carbon\Carbon::parse($case->released_date_resolution_mr)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="scanned_resolution_mr">{{ $case->scanned_resolution_mr ?? '-' }}</td>
        <td class="editable-cell" data-field="date_appeal_received_records" data-type="date">
            {{ $case->date_appeal_received_records ? \Carbon\Carbon::parse($case->date_appeal_received_records)->format('Y-m-d') : '-' }}
        </td>
        <td class="editable-cell" data-field="date_indorsed_office_secretary" data-type="date">
            {{ $case->date_indorsed_office_secretary ? \Carbon\Carbon::parse($case->date_indorsed_office_secretary)->format('Y-m-d') : '-' }}
        </td> --}}

        {{-- Additional Information --}}
        {{-- <td class="editable-cell" data-field="logbook_page_number">{{ $case->logbook_page_number ?? '-' }}</td>
        <td class="editable-cell" data-field="remarks_notes" title="{{ $case->remarks_notes ?? '' }}">
            {{ $case->remarks_notes ? Str::limit($case->remarks_notes, 30) : '-' }}
        </td> --}}

        {{-- Created At --}}
        <td class="non-editable">
            {{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}
        </td>
    </tr>
@endforeach