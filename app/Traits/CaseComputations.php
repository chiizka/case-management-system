<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait for automatic case field computations
 * Based on Excel formulas from the MIS system
 */
trait CaseComputations
{
    /**
     * Compute all auto-calculated fields
     * Call this method before saving a case
     */
    public function computeFields()
    {
        $this->computeLapseOf20DayPeriod();
        $this->computePctForDocketing();
        $this->computeAgingDocket();
        $this->computeStatusDocket();
        $this->compute1stMcPct();
        $this->computeStatus1stMc();
        $this->compute2ndLastMcPct();
        $this->computeStatus2ndMc();
        $this->computePoPct();
        $this->computeAgingPoPct();
        $this->computeStatusPoPct();
        $this->computePct96Days(); // ✅ NEW
    }

    /**
     * Lapse of 20 day Correction Period = Date of NR + 21 days
     */
    protected function computeLapseOf20DayPeriod()
    {
        if ($this->date_of_nr) {
            try {
                $dateOfNr = Carbon::parse($this->date_of_nr);
                $this->lapse_20_day_period = $dateOfNr->addDays(21)->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning("Error computing lapse_20_day_period: " . $e->getMessage());
                $this->lapse_20_day_period = null;
            }
        } else {
            $this->lapse_20_day_period = null;
        }
    }

    /**
     * PCT for Docketing = Lapse of 20 day Correction Period + 5 days
     */
    protected function computePctForDocketing()
    {
        if ($this->lapse_20_day_period) {
            try {
                $lapsePeriod = Carbon::parse($this->lapse_20_day_period);
                $this->pct_for_docketing = $lapsePeriod->addDays(5)->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning("Error computing pct_for_docketing: " . $e->getMessage());
                $this->pct_for_docketing = null;
            }
        } else {
            $this->pct_for_docketing = null;
        }
    }

    /**
     * Aging (Docket) = Date Scheduled/Docketed - PCT for Docketing
     * Returns number of days (positive when beyond deadline)
     */
    protected function computeAgingDocket()
    {
        if ($this->date_scheduled_docketed && $this->pct_for_docketing) {
            try {
                $dateScheduled = Carbon::parse($this->date_scheduled_docketed);
                $pctDocketing = Carbon::parse($this->pct_for_docketing);
                // Subtract: later date - earlier date gives positive when beyond
                $this->aging_docket = $pctDocketing->diffInDays($dateScheduled, false);
            } catch (\Exception $e) {
                \Log::warning("Error computing aging_docket: " . $e->getMessage());
                $this->aging_docket = null;
            }
        } else {
            $this->aging_docket = null;
        }
    }

    /**
     * Status (Docket) = IF(Aging Docket >= 1, "Beyond", "Within")
     */
    protected function computeStatusDocket()
    {
        if ($this->aging_docket !== null) {
            $this->status_docket = $this->aging_docket >= 1 ? 'Beyond' : 'Within';
        } else {
            $this->status_docket = null;
        }
    }

    /**
     * 1st MC PCT = Date of 1st MC (Actual) - Lapse of 20 day Correction Period
     * Returns number of days (positive when beyond deadline)
     */
    protected function compute1stMcPct()
    {
        if ($this->date_1st_mc_actual && $this->lapse_20_day_period) {
            try {
                $date1stMc = Carbon::parse($this->date_1st_mc_actual);
                $lapsePeriod = Carbon::parse($this->lapse_20_day_period);
                // Subtract: later date - earlier date gives positive when beyond
                $this->first_mc_pct = $lapsePeriod->diffInDays($date1stMc, false);
            } catch (\Exception $e) {
                \Log::warning("Error computing first_mc_pct: " . $e->getMessage());
                $this->first_mc_pct = null;
            }
        } else {
            $this->first_mc_pct = null;
        }
    }

    /**
     * Status (1st MC) = IF(1st MC PCT > 15, "Beyond", "Within")
     */
    protected function computeStatus1stMc()
    {
        if ($this->first_mc_pct !== null) {
            $this->status_1st_mc = $this->first_mc_pct > 15 ? 'Beyond' : 'Within';
        } else {
            $this->status_1st_mc = null;
        }
    }

    /**
     * 2nd/Last MC PCT = Date of 2nd MC - Date of 1st MC (Actual)
     * Returns number of days (positive when beyond deadline)
     */
    protected function compute2ndLastMcPct()
    {
        if ($this->date_2nd_last_mc && $this->date_1st_mc_actual) {
            try {
                $date2ndMc = Carbon::parse($this->date_2nd_last_mc);
                $date1stMc = Carbon::parse($this->date_1st_mc_actual);
                // Subtract: later date - earlier date gives positive when beyond
                $this->second_last_mc_pct = $date1stMc->diffInDays($date2ndMc, false);
            } catch (\Exception $e) {
                \Log::warning("Error computing second_last_mc_pct: " . $e->getMessage());
                $this->second_last_mc_pct = null;
            }
        } else {
            $this->second_last_mc_pct = null;
        }
    }

    /**
     * Status (2nd MC) = IF(2nd/Last MC PCT > 30, "Beyond", "Within")
     */
    protected function computeStatus2ndMc()
    {
        if ($this->second_last_mc_pct !== null) {
            $this->status_2nd_mc = $this->second_last_mc_pct > 30 ? 'Beyond' : 'Within';
        } else {
            $this->status_2nd_mc = null;
        }
    }

    /**
     * PO PCT = Lapse of 20 day Correction Period + 45 days
     */
    protected function computePoPct()
    {
        if ($this->lapse_20_day_period) {
            try {
                $lapsePeriod = Carbon::parse($this->lapse_20_day_period);
                $this->po_pct = $lapsePeriod->addDays(45)->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning("Error computing po_pct: " . $e->getMessage());
                $this->po_pct = null;
            }
        } else {
            $this->po_pct = null;
        }
    }

    /**
     * Aging (PO PCT) = Case Folder Forwarded to RO - PO PCT
     * Returns number of days (negative when within deadline, positive when beyond)
     */
    protected function computeAgingPoPct()
    {
        if ($this->case_folder_forwarded_to_ro && $this->po_pct) {
            try {
                $caseFolderDate = Carbon::parse($this->case_folder_forwarded_to_ro);
                $poPct = Carbon::parse($this->po_pct);
                // Subtract: later date - earlier date
                // Negative when case folder is before deadline (within)
                // Positive when case folder is after deadline (beyond)
                $this->aging_po_pct = $poPct->diffInDays($caseFolderDate, false);
            } catch (\Exception $e) {
                \Log::warning("Error computing aging_po_pct: " . $e->getMessage());
                $this->aging_po_pct = null;
            }
        } else {
            $this->aging_po_pct = null;
        }
    }

    /**
     * Status (PO PCT) = IF(Aging (PO PCT) <= 0, "Within", "Beyond")
     */
    protected function computeStatusPoPct()
    {
        if ($this->aging_po_pct !== null) {
            $this->status_po_pct = $this->aging_po_pct <= 0 ? 'Within' : 'Beyond';
        } else {
            $this->status_po_pct = null;
        }
    }

    /**
     * PCT 96 Days = Date of NR + 96 days
     */
    protected function computePct96Days()
    {
        if ($this->date_of_nr) {
            try {
                $dateOfNr = Carbon::parse($this->date_of_nr);
                $this->pct_96_days = $dateOfNr->addDays(96)->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning("Error computing pct_96_days: " . $e->getMessage());
                $this->pct_96_days = null;
            }
        } else {
            $this->pct_96_days = null;
        }
    }
}