<?php

class ConnectingLineList {

    var $list; 

    function __construct() {
        $this->list = [];
    }

    function AddConnectingLine($vh, $x, $y, $l) {
        array_push($this->list, [$vh, $x, $y, $l]);
    }

    function copyLine($L,$t,$l) {
        $C = $L;
        $C[1] += $l;
        $C[2] += $t;
        array_push($this->list, $C);
    }

    function drawAllToPDFCanvas(&$pAll, &$fpdf) {
        $t2 = $pAll->treeData['line_thickness']/2;
        $fpdf->SetFillColor($pAll->treeData['connecting_R'],$pAll->treeData['connecting_G'],$pAll->treeData['connecting_B']);
        foreach($this->list as $CLi) {
            if($CLi[0]=='v') {
                $fpdf->Rect($CLi[1]-$t2,$CLi[2],2*$t2,$CLi[3],'F');
            } else { // h
                $fpdf->Rect($CLi[1],$CLi[2]-$t2,$CLi[3],2*$t2,'F');
                $fpdf->Circle($CLi[1],$CLi[2],$t2,'F');
                $fpdf->Circle($CLi[1]+$CLi[3],$CLi[2],$t2,'F');
            }
        }
    }
}
