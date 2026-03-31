<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait for automatic case field computations
 * Formulas sourced directly from Excel MIS sheet (Dates_-_Sample.xlsx)
 *
 * Column reference (Excel → DB field):
 *   I  = date_of_nr
 *   J  = lapse_20_day_period       =I+21
 *   K  = pct_for_docketing         =J+5
 *   L  = date_scheduled_docketed
 *   M  = aging_docket              =L-K  (positive = beyond deadline)
 *   N  = status_docket             =IF(M>=1,"Beyond","Within")
 *   Q  = date_1st_mc_actual
 *   R  = first_mc_pct              =Q-J  (positive = beyond deadline)
 *   S  = status_1st_mc             =IF(R>15,"Beyond","Within")
 *   T  = date_2nd_last_mc
 *   U  = second_last_mc_pct        =T-Q  (positive = beyond deadline)
 *   V  = status_2nd_mc             =IF(U>30,"Beyond","Within")
 *   W  = case_folder_forwarded_to_ro
 *   AA = po_pct                    =J+45
 *   AB = aging_po_pct              =W-AA (positive = beyond deadline)
 *   AC = status_po_pct             =IF(AB<=0,"Within","Beyond")
 *   AU = pct_96_days               =I+96
 *   AV = date_signed_mis
 *   AW = status_pct                =IF(AV>AU,"Beyond","Within")
 *   AX = reference_date_pct
 *   AY = aging_pct                 =AX-I
 */
trait CaseComputations
{
    /**
     * Compute all auto-calculated fields.
     * Call this method before saving a case (on create or inline update).
     */
    public function computeFields(): void
    {
        // Chain order matters — each step may depend on the previous one
        $this->computeLapseOf20DayPeriod();   // J = I + 21
        $this->computePctForDocketing();       // K = J + 5
        $this->computeAgingDocket();           // M = L - K
        $this->computeStatusDocket();          // N = IF(M>=1,Beyond,Within)
        $this->compute1stMcPct();              // R = Q - J
        $this->computeStatus1stMc();           // S = IF(R>15,Beyond,Within)
        $this->compute2ndLastMcPct();          // U = T - Q
        $this->computeStatus2ndMc();           // V = IF(U>30,Beyond,Within)
        $this->computePoPct();                 // AA = J + 45
        $this->computeAgingPoPct();            // AB = W - AA
        $this->computeStatusPoPct();           // AC = IF(AB<=0,Within,Beyond)
        $this->computePct96Days();             // AU = I + 96
        $this->computeStatusPct();             // AW = IF(AV>AU,Beyond,Within)
        $this->computeAgingPct();              // AY = AX - I
    }

    // =========================================================================
    // INSPECTION STAGE
    // =========================================================================

    /**
     * J = I + 21
     * Lapse of 20-day Correction Period = Date of NR + 21 days
     */
    protected function computeLapseOf20DayPeriod(): void
    {
        if ($this->date_of_nr) {
            try {
                $this->lapse_20_day_period = Carbon::parse($this->date_of_nr)->addDays(21);
            } catch (\Exception $e) {
                \Log::warning("computeLapseOf20DayPeriod error: {$e->getMessage()}");
                $this->lapse_20_day_period = null;
            }
        } else {
            $this->lapse_20_day_period = null;
        }
    }

    // =========================================================================
    // DOCKETING STAGE
    // =========================================================================

    /**
     * K = J + 5
     * PCT for Docketing = Lapse of 20-day period + 5 days
     */
    protected function computePctForDocketing(): void
    {
        if ($this->lapse_20_day_period) {
            try {
                $this->pct_for_docketing = Carbon::parse($this->lapse_20_day_period)->addDays(5);
            } catch (\Exception $e) {
                \Log::warning("computePctForDocketing error: {$e->getMessage()}");
                $this->pct_for_docketing = null;
            }
        } else {
            $this->pct_for_docketing = null;
        }
    }

    /**
     * M = L - K
     * Aging (Docket) = Date Scheduled/Docketed − PCT for Docketing
     * Positive value = case was docketed AFTER the deadline (beyond).
     * Negative value = case was docketed BEFORE the deadline (within).
     */
    protected function computeAgingDocket(): void
    {
        if ($this->date_scheduled_docketed && $this->pct_for_docketing) {
            try {
                $dateScheduled = Carbon::parse($this->date_scheduled_docketed);
                $pctDocketing  = Carbon::parse($this->pct_for_docketing);
                // diffInDays($other, false): positive when $dateScheduled is AFTER $pctDocketing
                $this->aging_docket = (int) $pctDocketing->diffInDays($dateScheduled, false);
            } catch (\Exception $e) {
                \Log::warning("computeAgingDocket error: {$e->getMessage()}");
                $this->aging_docket = null;
            }
        } else {
            $this->aging_docket = null;
        }
    }

    /**
     * N = IF(M >= 1, "Beyond", "Within")
     */
    protected function computeStatusDocket(): void
    {
        if ($this->aging_docket !== null) {
            $this->status_docket = $this->aging_docket >= 1 ? 'Beyond' : 'Within';
        } else {
            $this->status_docket = null;
        }
    }

    // =========================================================================
    // HEARING PROCESS STAGE
    // =========================================================================

    /**
     * R = Q - J
     * 1st MC PCT = Date of 1st MC (Actual) − Lapse of 20-day Correction Period
     * Positive = beyond the 15-day target.
     */
    protected function compute1stMcPct(): void
    {
        if ($this->date_1st_mc_actual && $this->lapse_20_day_period) {
            try {
                $date1stMc   = Carbon::parse($this->date_1st_mc_actual);
                $lapsePeriod = Carbon::parse($this->lapse_20_day_period);
                // positive when date_1st_mc is AFTER lapse_period
                $this->first_mc_pct = (int) $lapsePeriod->diffInDays($date1stMc, false);
            } catch (\Exception $e) {
                \Log::warning("compute1stMcPct error: {$e->getMessage()}");
                $this->first_mc_pct = null;
            }
        } else {
            $this->first_mc_pct = null;
        }
    }

    /**
     * S = IF(R > 15, "Beyond", "Within")
     */
    protected function computeStatus1stMc(): void
    {
        if ($this->first_mc_pct !== null) {
            $this->status_1st_mc = $this->first_mc_pct > 15 ? 'Beyond' : 'Within';
        } else {
            $this->status_1st_mc = null;
        }
    }

    /**
     * U = T - Q
     * 2nd/Last MC PCT = Date of 2nd MC − Date of 1st MC
     * Positive = beyond the 30-day target.
     */
    protected function compute2ndLastMcPct(): void
    {
        if ($this->date_2nd_last_mc && $this->date_1st_mc_actual) {
            try {
                $date2ndMc = Carbon::parse($this->date_2nd_last_mc);
                $date1stMc = Carbon::parse($this->date_1st_mc_actual);
                // positive when date_2nd is AFTER date_1st
                $this->second_last_mc_pct = (int) $date1stMc->diffInDays($date2ndMc, false);
            } catch (\Exception $e) {
                \Log::warning("compute2ndLastMcPct error: {$e->getMessage()}");
                $this->second_last_mc_pct = null;
            }
        } else {
            $this->second_last_mc_pct = null;
        }
    }

    /**
     * V = IF(U > 30, "Beyond", "Within")
     */
    protected function computeStatus2ndMc(): void
    {
        if ($this->second_last_mc_pct !== null) {
            $this->status_2nd_mc = $this->second_last_mc_pct > 30 ? 'Beyond' : 'Within';
        } else {
            $this->status_2nd_mc = null;
        }
    }

    // =========================================================================
    // REVIEW & DRAFTING STAGE
    // =========================================================================

    /**
     * AA = J + 45
     * PO PCT = Lapse of 20-day Correction Period + 45 days
     */
    protected function computePoPct(): void
    {
        if ($this->lapse_20_day_period) {
            try {
                $this->po_pct = Carbon::parse($this->lapse_20_day_period)->addDays(45);
            } catch (\Exception $e) {
                \Log::warning("computePoPct error: {$e->getMessage()}");
                $this->po_pct = null;
            }
        } else {
            $this->po_pct = null;
        }
    }

    /**
     * AB = W - AA
     * Aging (PO PCT) = Case Folder Forwarded to RO (Actual) − PO PCT
     * Positive  = folder was forwarded AFTER the deadline (beyond).
     * Zero/Neg  = folder was forwarded ON or BEFORE the deadline (within).
     */
    protected function computeAgingPoPct(): void
    {
        if ($this->case_folder_forwarded_to_ro && $this->po_pct) {
            try {
                $caseFolderDate = Carbon::parse($this->case_folder_forwarded_to_ro);
                $poPct          = Carbon::parse($this->po_pct);
                // positive when case_folder_date is AFTER po_pct
                $this->aging_po_pct = (int) $poPct->diffInDays($caseFolderDate, false);
            } catch (\Exception $e) {
                \Log::warning("computeAgingPoPct error: {$e->getMessage()}");
                $this->aging_po_pct = null;
            }
        } else {
            $this->aging_po_pct = null;
        }
    }

    /**
     * AC = IF(AB <= 0, "Within", "Beyond")
     */
    protected function computeStatusPoPct(): void
    {
        if ($this->aging_po_pct !== null) {
            $this->status_po_pct = $this->aging_po_pct <= 0 ? 'Within' : 'Beyond';
        } else {
            $this->status_po_pct = null;
        }
    }

    // =========================================================================
    // ORDERS & DISPOSITION STAGE
    // =========================================================================

    /**
     * AU = I + 96
     * PCT (96 days) = Date of NR + 96 days
     */
    protected function computePct96Days(): void
    {
        if ($this->date_of_nr) {
            try {
                $this->pct_96_days = Carbon::parse($this->date_of_nr)->addDays(96);
            } catch (\Exception $e) {
                \Log::warning("computePct96Days error: {$e->getMessage()}");
                $this->pct_96_days = null;
            }
        } else {
            $this->pct_96_days = null;
        }
    }

    /**
     * AW = IF(AV > AU, "Beyond", "Within")
     * Status (PCT) = IF(Date Signed (MIS) > PCT 96 days, "Beyond", "Within")
     *
     * Previously missing — added from Excel formula =IF(AV3>AU3,"Beyond","Within")
     */
    protected function computeStatusPct(): void
    {
        if ($this->date_signed_mis && $this->pct_96_days) {
            try {
                $dateSigned = Carbon::parse($this->date_signed_mis);
                $pct96      = Carbon::parse($this->pct_96_days);
                $this->status_pct = $dateSigned->gt($pct96) ? 'Beyond' : 'Within';
            } catch (\Exception $e) {
                \Log::warning("computeStatusPct error: {$e->getMessage()}");
                $this->status_pct = null;
            }
        } else {
            $this->status_pct = null;
        }
    }

    /**
     * AY = AX - I
     * Aging (PCT) = Reference Date (PCT) − Date of NR
     * Should be less than 96 days.
     *
     * Previously missing — added from Excel formula =AX3-I3
     */
    protected function computeAgingPct(): void
    {
        if ($this->reference_date_pct && $this->date_of_nr) {
            try {
                $refDate   = Carbon::parse($this->reference_date_pct);
                $dateOfNr  = Carbon::parse($this->date_of_nr);
                // positive when refDate is AFTER date_of_nr
                $this->aging_pct = (int) $dateOfNr->diffInDays($refDate, false);
            } catch (\Exception $e) {
                \Log::warning("computeAgingPct error: {$e->getMessage()}");
                $this->aging_pct = null;
            }
        } else {
            $this->aging_pct = null;
        }
    }
}