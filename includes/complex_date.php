<?php

class CDateValue
{

    var $StructureToInt = array(
        "Exact" => 0,
        "From" => 1,
        "To" => 2,
        "FromTo" => 3,
        "Before" => 4,
        "After" => 5,
        "Between" => 6,
        "About" => 7,
        "Calculated" => 8,
        "Estimated" => 9,
        "Interpreted" => 10);
    var $IntToStructure = array(
        "Exact",
        "From",
        "To",
        "FromTo",
        "Before",
        "After",
        "Between",
        "About",
        "Calculated",
        "Estimated",
        "Interpreted");
    var $Date1 = array(
        "iday" => -1,
        "imonth" => -1,
        "iyear" => -1);
    var $Date2 = array(
        "iday" => -1,
        "imonth" => -1,
        "iyear" => -1);
    var $InterpretedString = "";
    var $Structure = 10;
    var $ShortFormMonth = array("Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    var $FullFormMonth = array("January", "February", "March", "April", "May",
        "June", "July", "August", "Sepember", "October", "November", "December");
    var $GEDCOMFormMonth = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN",
        "JUL", "AUG", "SEP", "OCT", "NOV", "DEC");

    function __construct() {
        $this->ClearData();
    }

    function ReadDatabase($mysqli, $date_id) {
        $data = read_assoc($mysqli, "complex_date", $date_id);
        $this->Structure = $data['structure'];
        $this->Date1['iday'] = $data['day1'];
        $this->Date1['imonth'] = $data['month1'];
        $this->Date1['iyear'] = $data['year1'];
        $this->Date2['iday'] = $data['day2'];
        $this->Date2['imonth'] = $data['month2'];
        $this->Date2['iyear'] = $data['year2'];
        $this->InterpretedString = $data['interpreted_string'];
    }

    function ClearData() {
        $this->Structure = $this->StructureToInt['Interpreted'];
        $this->Date1['iday'] = -1;
        $this->Date1['imonth'] = -1;
        $this->Date1['iyear'] = -1;
        $this->Date2['iday'] = -1;
        $this->Date2['imonth'] = -1;
        $this->Date2['iyear'] = -1;
        $this->InterpretedString = "";
    }

    function UpdateDatabase($mysqli, $date_id) {
        $data = array(
            "structure" => $this->Structure,
            "day1" => $this->Date1['iday'],
            "month1" => $this->Date1['imonth'],
            "year1" => $this->Date1['iyear'],
            "day2" => $this->Date2['iday'],
            "month2" => $this->Date2['imonth'],
            "year2" => $this->Date2['iyear'],
            "interpreted_string" => $this->InterpretedString);
        update_assoc($mysqli, "complex_date", $date_id, $data);
    }

    function isInterpreted() {
        return $this->Structure == $this->StructureToInt['Interpreted'];
    }
    
    function Interpret($DateStr) {
        // echo $DateStr . '<br>';
        $this->ClearData();
        $sDate = trim(strtoupper($DateStr));
        while (strpos($sDate, "INT") === 0) {
            $sDate = trim(substr($sDate, 3));
        }
        $this->InterpretedString = $sDate;
        $this->Structure = $this->StructureToInt['Interpreted'];
        $OK = true;
        $pFrom = strpos($sDate, "FROM");
        if ($pFrom === false) {
            $pFrom = -1;
        }
        $pTo = strpos($sDate, "TO");
        if ($pTo === false) {
            $pTo = -1;
        }
        $pBef = strpos($sDate, "BEF");
        if ($pBef === false) {
            $pBef = -1;
        }
        $pAft = strpos($sDate, "AFT");
        if ($pAft === false) {
            $pAft = -1;
        }
        $pBet = strpos($sDate, "BET");
        if ($pBet === false) {
            $pBet = -1;
        }
        $pAnd = strpos($sDate, "AND");
        if ($pAnd === false) {
            $pAnd = -1;
        }
        $pAbt = strpos($sDate, "ABT");
        if ($pAbt === false) {
            $pAbt = -1;
        }
        $pCal = strpos($sDate, "CAL");
        if ($pCal === false) {
            $pCal = -1;
        }
        $pEst = strpos($sDate, "EST");
        if ($pEst === false) {
            $pEst = -1;
        }

        if (($pFrom == 0) && ($pTo > 0)) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 4, $pTo), $this->Date1);
            $OK = $OK && $this->InterpretExactDate(substr($sDate, $pTo + 2), $this->Date2);
            if ($OK) {
                $this->Structure = $this->StructureToInt['FromTo'];
            }
        } else if ($pFrom == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 4), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['From'];
            }
        } else if ($pTo == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 2), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['To'];
            }
        } else if ($pBef == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['Before'];
            }
        } else if ($pAft == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['After'];
            }
        } else if ($pAbt == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['About'];
            }
        } else if ($pCal == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['Calculated'];
            }
        } else if ($pEst == 0) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3), $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['Estimated'];
            }
        } else if (($pBet == 0) && ($pAnd > 0)) {
            $OK = $OK && $this->InterpretExactDate(substr($sDate, 3, $pAnd), $this->Date1);
            $OK = $OK && $this->InterpretExactDate(substr($sDate, $pAnd + 3), $this->Date2);
            if ($OK) {
                $this->Structure = $this->StructureToInt['Between'];
            }
        } else {
            $OK = $OK && $this->InterpretExactDate($sDate, $this->Date1);
            if ($OK) {
                $this->Structure = $this->StructureToInt['Exact'];
            }
        }
        // echo $this->FullStyle() . '<br>';
        // if($OK) {echo "OK<br>"; } else {echo "Not OK<br>"; }
        return $OK;
    }

    function IsBlank() {
        return (strlen($this->InterpretedString) == 0);
    }

    function GEDCOMStyle() {
        $rtn = "To be overwritten";
        if ($this->Structure == $this->StructureToInt['Exact']) {
            $rtn = $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['From']) {
            $rtn = "FROM " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['To']) {
            $rtn = "TO " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['FromTo']) {
            $rtn = "FROM " . $this->GEDCOMStyleDate($this->Date1) . " TO " . $this->GEDCOMStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['Before']) {
            $rtn = "BEF " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['After']) {
            $rtn = "AFT " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Between']) {
            $rtn = "BET " . $this->GEDCOMStyleDate($this->Date1) . " AND " . $this->GEDCOMStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['About']) {
            $rtn = "ABT " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Calculated']) {
            $rtn = "CAL " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Estimated']) {
            $rtn = "EST " . $this->GEDCOMStyleDate($this->Date1);
        } else {
            $rtn = "INT " . $this->InterpretedString;
        }
        return $rtn;
    }

    function EditStyle() {
        $rtn = "To be overwritten";
        if ($this->Structure == $this->StructureToInt['Exact']) {
            $rtn = $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['From']) {
            $rtn = "FROM " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['To']) {
            $rtn = "TO " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['FromTo']) {
            $rtn = "FROM " . $this->GEDCOMStyleDate($this->Date1) . " TO " . $this->GEDCOMStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['Before']) {
            $rtn = "BEF " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['After']) {
            $rtn = "AFT " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Between']) {
            $rtn = "BET " . $this->GEDCOMStyleDate($this->Date1) . " AND " . $this->GEDCOMStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['About']) {
            $rtn = "ABT " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Calculated']) {
            $rtn = "CAL " . $this->GEDCOMStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Estimated']) {
            $rtn = "EST " . $this->GEDCOMStyleDate($this->Date1);
        } else {
            $rtn = $this->InterpretedString;
        }
        return $rtn;
    }

    function RoughDoB() {
        $year = 0;
        $month = 0;
        $day = 0;
        if (
            ($this->Structure == $this->StructureToInt['Exact']) ||
            ($this->Structure == $this->StructureToInt['From']) ||
            ($this->Structure == $this->StructureToInt['To']) ||
            ($this->Structure == $this->StructureToInt['Before']) ||
            ($this->Structure == $this->StructureToInt['After']) || 
            ($this->Structure == $this->StructureToInt['About']) || 
            ($this->Structure == $this->StructureToInt['Calculated']) ||
            ($this->Structure == $this->StructureToInt['Estimated']) ) 
        {
            if ($this->Date1['iyear'] >= 0) $year = $this->Date1['iyear'];
            if ($this->Date1['imonth'] >= 0) $month = $this->Date1['imonth'];
            if ($this->Date1['iday'] >= 0) $day = $this->Date1['iday'];

        } else if (
            ($this->Structure == $this->StructureToInt['FromTo']) ||
            ($this->Structure == $this->StructureToInt['Between']) ) 
        {
            if ($this->Date2['iyear'] >= 0) $year = $this->Date2['iyear'];
            if ($this->Date2['imonth'] >= 0) $month = $this->Date2['imonth'];
            if ($this->Date2['iday'] >= 0) $day = $this->Date2['iday'];
        } 
        return $year + $month/100.0 + $day/10000.0;
    }

    function FullStyle() {
        $rtn = "To be overwritten";
        if ($this->Structure == $this->StructureToInt['Exact']) {
            $rtn = $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['From']) {
            $rtn = "From " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['To']) {
            $rtn = "To " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['FromTo']) {
            $rtn = "From " . $this->FullStyleDate($this->Date1) . " To " . $this->FullStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['Before']) {
            $rtn = "Before " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['After']) {
            $rtn = "After " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Between']) {
            $rtn = "Between " . $this->FullStyleDate($this->Date1) . " and " . $this->FullStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['About']) {
            $rtn = "About " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Calculated']) {
            $rtn = "Calculated " . $this->FullStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Estimated']) {
            $rtn = "Estimated " . $this->FullStyleDate($this->Date1);
        } else if (strlen($this->InterpretedString) == 0) {
            $rtn = "No date";
        } else {
            $rtn = $this->InterpretedString;
        }
        return $rtn;
    }

    function ShortStyle() {
        $rtn = "To be overwritten";
        if ($this->Structure == $this->StructureToInt['Exact']) {
            $rtn = $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['From']) {
            $rtn = $this->ShortStyleDate($this->Date1) . " -";
        } else if ($this->Structure == $this->StructureToInt['To']) {
            $rtn = "- " . $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['FromTo']) {
            $rtn = $this->ShortStyleDate($this->Date1) . " - " . $this->ShortStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['Before']) {
            $rtn = "< " . $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['After']) {
            $rtn = "> " . $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Between']) {
            $rtn = $this->ShortStyleDate($this->Date1) . " - " . $this->ShortStyleDate($this->Date2);
        } else if ($this->Structure == $this->StructureToInt['About']) {
            $rtn = "~ " . $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Calculated']) {
            $rtn = "Calc " . $this->ShortStyleDate($this->Date1);
        } else if ($this->Structure == $this->StructureToInt['Estimated']) {
            $rtn = "Est. " . $this->ShortStyleDate($this->Date1);
        } else {
            $rtn = $this->InterpretedString;
        }
        return $rtn;
    }

    function ShortStyleDate($ED) {
        $cDay = strval($ED['iday']);
        $cYear = strval($ED['iyear']);
        $rtn = "";
        if (($ED['iday'] > 0) && ($ED['imonth'] > 0)) {
            $rtn = $cDay . " " . $this->ShortFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else if ($ED['imonth'] > 0) {
            $rtn = $this->ShortFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else {
            $rtn = $cYear;
        }
        return $rtn;
    }

    function FullStyleDate($ED) {
        $cDay = strval($ED['iday']);
        $cYear = strval($ED['iyear']);
        $rtn = "";
        if (($ED['iday'] > 0) && ($ED['imonth'] > 0)) {
            $rtn = $cDay . " " . $this->FullFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else if ($ED['imonth'] > 0) {
            $rtn = $this->FullFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else {
            $rtn = $cYear;
        }
        return $rtn;
    }

    function GEDCOMStyleDate($ED) {
        $cDay = strval($ED['iday']);
        $cYear = strval($ED['iyear']);
        $rtn = "";
        if (($ED['iday'] > 0) && ($ED['imonth'] > 0)) {
            $rtn = $cDay . " " . $this->GEDCOMFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else if ($ED['imonth'] > 0) {
            $rtn = $this->GEDCOMFormMonth[$ED['imonth'] - 1] . " " . $cYear;
        } else {
            $rtn = $cYear;
        }
        return $rtn;
    }

    function InterpretExactDate($EDin, &$EDout) {
        // echo $EDin . "<br>";
        $EDout = array(
            "iday" => -1,
            "imonth" => -1,
            "iyear" => -1);
        $OK = true;
        $sDay = "";
        $sYear = "";
        $ED = trim($EDin);
        $pMonth = 0;
        $Day = 0;
        $Mon = 0;
        $Year = 0;

        for ($i = 0; $i < 12; $i++) {
            $pMonth = strpos($ED, $this->GEDCOMFormMonth[$i]);
            if ($pMonth != false) {
                $Mon = $i + 1;
                $sDay = trim(substr($ED, 0, $pMonth));
                $sYear = trim(substr($ED, $pMonth + 3));
            }
        }

        if ($Mon == 0) {
            $sYear = $ED;
        } else {
            $EDout['imonth'] = $Mon;
        }

        // echo 'sDay = "' . $sDay . '", mon = "' . $Mon . '", sYear = "' . $sYear . '"<br>';
        
        if (strlen($sDay) > 0) {
            $Day = intval($sDay);
            if (($Day < 1) || ($Day > 31)) {
                $OK = false;
            } else {
                $EDout['iday'] = $Day;
                // echo 'Day = ' . $Day . '<br>';
            }
        }

        if (strlen($sYear) > 0) {
            $Year = intval($sYear);
            if (($Year < 1) || ($Year > 3000)) {
                $OK = false;
            } else {
                $EDout['iyear'] = $Year;
                // echo 'Year = ' . $Year . '<br>';
            }
        }

        // print_r($EDout);
        // if($OK) {echo "OK<br>"; } else {echo "Not OK<br>"; }
        return $OK;
    }

    function subtractYrs($y) {
        if ($this->Date1['iYear'] != -1) {
            $this->Date1['iYear'] -= $y;
        }
        if ($this->Date2['iYear'] != -1) {
            $this->Date1['iYear'] -= $y;
        }
    }

}
