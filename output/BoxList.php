<?php

class BoxList
{
    /*
      public enum BoxType {
      Unknown, IndividualType (individual), MarriageType (family), TitleType, AuthorType,
      missingUpLink, missingDnLink
      };
     */

    var $list;

    function __construct()
    {
        $this->list = [];
    }

    function AddBox($type, $id, $xi, $yi, $wi, $hi)
    {
        switch ($type) {
            case "AuthorType":
                $link = '/edit/edit_options.php';
                break;
            case "individual":
                $link = '/edit/edit_individual.php?i=' . $id;
                break;
            case "family":
            case "missingUpLink":
            case "missingDnLink":
                $link = '/edit/edit_family.php?f=' . $id;
                break;
            case "TitleType":
                $link = '/edit/edit_options.php';
                break;
            default:
                $link = '/index.php';
        }
        array_push($this->list, [
            $type, // [0] = type of box = AuthorType, individual, family or TitleType
            $id, // [1] = id of the individual or family
            $xi, // [2] = x coordinate of top left of box
            $yi, // [3] = y coordinate of top left of box
            $wi, // [4] = width of box
            $hi, // [5] = height of box
            $link, // [6] = link to be followed if box is clicked
            null // [7] = image of box - only used when not PDF and for AuthorType, individual or TitleType
        ]);
    }

    function copyBox($B, $t, $l)
    {
        $C = $B;
        $C[2] += $l;
        $C[3] += $t;
        array_push($this->list, $C);
    }

    function getBox($type, $id)
    {
        $ret = null;
        foreach ($this->list as $key => $box) {
            if (($box[0] == $type) && ($box[1] == $id)) {
                $ret = $key;
            }
        }
        return $ret;
    }

    function LocateBox($px, $py)
    {
        $ret = null;
        foreach ($this->list as $key => $box) {
            if (($px >= $box[2]) && ($px <= $box[2] + $box[4]) && ($py >= $box[3]) && ($py <= $box[3] + $box[5])) {
                $ret = $key;
            }
        }
        return $ret;
    }

    function drawAllToPDFCanvas(&$pAll, &$fpdf, $withlinks)
    // Note: missing up and down links are not drawn on the PDF canvas
    {
        foreach ($this->list as &$box) {
            $type = $box[0];
            $x0 = $box[2];
            $y0 = $box[3];
            if ($withlinks) {
                $fpdf->Link($x0, $y0, $box[4], $box[5], $box[6]);
            }
            if ($type == "AuthorType") {
                $fpdf->setAGFont($pAll->originator_font);
                if ($pAll->originator_font['opaque_background']) {
                    $fpdf->Rect($x0, $y0, $box[4], $box[5], 'F');
                }
                $LH = $pAll->originator_font['size'];
                $fpdf->setXY($x0, $y0);
                $fpdf->Cell($box[4], $LH, $pAll->authorText, 0, 2, 'L', false);
                $fpdf->Cell($box[4], $LH, $pAll->TruTreeCredit, 0, 2, 'L', false);
                if ($pAll->Needs_Asterisk_Explanation) {
                    $fpdf->Cell($box[4], $LH, $pAll->AstriskExplanation, 0, 2, 'L', false);
                }
            } else if ($type == "individual") {
                $fpdf->SetFillColor($pAll->treeData['outline_R'], $pAll->treeData['outline_G'], $pAll->treeData['outline_B']);
                $t = $pAll->treeData['outline_thickness'];
                $x = $x0;
                $y = $y0;
                $w = $box[4];
                $h = $box[5];
                if ($pAll->treeData['box_outline']) {
                    $fpdf->Rect($x, $y, $w, $t, 'F');
                    $fpdf->Rect($x, $y, $t, $h, 'F');
                    $fpdf->Rect($x, $y + $h, $w, -$t, 'F');
                    $fpdf->Rect($x + $w, $y, -$t, $h, 'F');
                    $x += $t;
                    $y += $t;
                    $w -= 2 * $t;
                    $h -= 2 * $t;
                }

                $flf = $pAll->first_line_font;
                $flfh = $flf['size'];
                $olf = $pAll->other_line_font;
                if ($flf['opaque_background']) {
                    $fpdf->SetFillColor($flf['background_R'], $flf['background_G'], $flf['background_B']);
                    $fpdf->Rect($x, $y, $w, $flfh, 'F');
                }
                if ($olf['opaque_background']) {
                    $fpdf->SetFillColor($olf['background_R'], $olf['background_G'], $olf['background_B']);
                    $fpdf->Rect($x, $y + $flfh, $w, $h - $flfh, 'F');
                }

                // Look at media thumbnail sizes
                if ($pAll->People[$box[1]]['l_media_thumb'] === null) {
                    $lw = 0;
                    $lh = 0;
                } else {
                    $lw = imagesx($pAll->People[$box[1]]['l_media_thumb']);
                    $lh = imagesy($pAll->People[$box[1]]['l_media_thumb']);
                    ob_start(); // Let's start output buffering.
                    imagejpeg($pAll->People[$box[1]]['l_media_thumb']); //This will normally output the image, but because of ob_start(), it won't.
                    $contents = ob_get_contents(); //Instead, output above is saved to $contents
                    ob_end_clean(); //End the output buffer.
                    $pic = 'data://text/plain;base64, ' . base64_encode($contents);
                    $fpdf->Image($pic, $x, $y, $lw, $lh, 'jpg');
                }
                if ($pAll->People[$box[1]]['r_media_thumb'] === null) {
                    $rw = 0;
                    $rh = 0;
                } else {
                    $rw = imagesx($pAll->People[$box[1]]['r_media_thumb']);
                    $rh = imagesy($pAll->People[$box[1]]['r_media_thumb']);
                    ob_start(); // Let's start output buffering.
                    imagejpeg($pAll->People[$box[1]]['r_media_thumb']); //This will normally output the image, but because of ob_start(), it won't.
                    $contents = ob_get_contents(); //Instead, output above is saved to $contents
                    ob_end_clean(); //End the output buffer.
                    $pic = 'data://text/plain;base64, ' . base64_encode($contents);
                    $fpdf->Image($pic, $x + $w - $rw, $y, $rw, $rh, 'jpg');
                }

                // Now get the text and decide whether there are multiple lines
                $lines = strToArray($pAll->People[$box[1]]['box_text']);
                $x += $lw;
                $w -= $lw;
                $w -= $rw;

                $fpdf->setAGFont($flf);
                $fpdf->setXY($x, $y);
                $LH = $flf['size'];
                for ($l = 0; $l < count($lines); $l++) {
                    if ($l == 1) {
                        $fpdf->setAGFont($olf);
                        $LH = $olf['size'];
                    }
                    $fpdf->Cell($w, $LH, $lines[$l], 0, 2, 'C', false);
                }
            } else if ($type == "TitleType") {
                $fpdf->setAGFont($pAll->title_font);
                $fpdf->setXY($x0, $y0);
                $fpdf->Cell($box[4], $box[5], $pAll->Title, 0, 2, 'C', $pAll->title_font['opaque_background']);
            }
        }
    }

    function drawAllToImgCanvas(&$pAll)
    {
        foreach ($this->list as &$box) {
            $type = $box[0];
            if ($type == "family") {
                $box[7] = null;
            } else {
                $im = imagecreatetruecolor($box[4], $box[5]);
                $tp = imagecolorallocatealpha($im, 255, 255, 255, 127);
                imagefill($im, 0, 0, $tp);  // set the transparent colour as the background.
                imagecolortransparent($im, $tp); // actually make it transparent

                if ($type == "AuthorType") {
                    if ($pAll->originator_font['opaque_background']) {
                        $bg = imagecolorallocate($im, $pAll->originator_font['background_R'], $pAll->originator_font['background_G'], $pAll->originator_font['background_B']);
                        imagefilledrectangle($im, 0, 0, $box[4], $box[5], $bg);
                    }
                    $top = 0;
                    $top = imagetextout($im, 0, $top, $pAll->originator_font, $pAll->authorText, false);
                    $top = imagetextout($im, 0, $top, $pAll->originator_font, $pAll->TruTreeCredit, false);
                    if ($pAll->Needs_Asterisk_Explanation) {
                        $top = imagetextout($im, 0, $top, $pAll->originator_font, $pAll->AstriskExplanation, false);
                    }
                } else if ($type == "individual") {
                    // Get the text and decide whether there are multiple lines
                    $lines = strToArray($pAll->People[$box[1]]['box_text']);
                    $outline = imagecolorallocate($im, $pAll->treeData['outline_R'], $pAll->treeData['outline_G'], $pAll->treeData['outline_B']);
                    $t = $pAll->treeData['outline_thickness'];
                    $x = 0;
                    $y = 0;
                    $w = $box[4];
                    $h = $box[5];
                    if ($pAll->treeData['box_outline']) {
                        imagefilledrectangle($im, $x, $y, $x + $w, $y + $t, $outline);
                        imagefilledrectangle($im, $x, $y, $x + $t, $y + $h, $outline);
                        imagefilledrectangle($im, $x, $y + $h, $x + $w, $y + $h - $t, $outline);
                        imagefilledrectangle($im, $x + $w, $y, $x + $w - $t, $y + $h, $outline);
                        $x += $t;
                        $y += $t;
                        $w -= 2 * $t;
                        $h -= 2 * $t;
                    }
                    $flf = $pAll->first_line_font;
                    $olf = $pAll->other_line_font;
                    $bbox = imagetextbox($flf['size'], getFontFile($flf), $lines[0]);
                    $flfh = $bbox['h'];
                    if ($flf['opaque_background']) {
                        $flfc = imagecolorallocate($im, $flf['background_R'], $flf['background_G'], $flf['background_B']);
                        imagefilledrectangle($im, $x, $y, $x + $w, $y + $flfh, $flfc);
                    }
                    // if (($pAll->other_line_font['opaque_background'])&&(count($lines)>1)) {
                    if ($olf['opaque_background']) {
                        $olfc = imagecolorallocate($im, $olf['background_R'], $olf['background_G'], $olf['background_B']);
                        imagefilledrectangle($im, $x, $y + $flfh, $x + $w, $y + $h, $olfc);
                    }

                    // Look at media thumbnail sizes and draw the thumbnails
                    if ($pAll->People[$box[1]]['l_media_thumb'] === null) {
                        $lw = 0;
                        $lh = 0;
                    } else {
                        $lw = imagesx($pAll->People[$box[1]]['l_media_thumb']);
                        $lh = imagesy($pAll->People[$box[1]]['l_media_thumb']);
                        imagecopy($im, $pAll->People[$box[1]]['l_media_thumb'], $x, $y, 0, 0, $lw, $lh);
                    }
                    if ($pAll->People[$box[1]]['r_media_thumb'] === null) {
                        $rw = 0;
                        $rh = 0;
                    } else {
                        $rw = imagesx($pAll->People[$box[1]]['r_media_thumb']);
                        $rh = imagesy($pAll->People[$box[1]]['r_media_thumb']);
                        imagecopy($im, $pAll->People[$box[1]]['r_media_thumb'], $x + $w - $rw, $y, 0, 0, $rw, $rh);
                    }

                    $x += $lw + ($w - $lw - $rw) / 2.0;

                    $font = $flf;
                    for ($l = 0; $l < count($lines); $l++) {
                        if ($l == 1) {
                            $font = $olf;
                        }
                        $y = imagetextout($im, $x, $y, $font, $lines[$l], true);
                    }
                } else if (($type == "missingUpLink")||($type == "missingDnLink")) {
                    $outline = imagecolorallocate($im, $pAll->treeData['connecting_R'], $pAll->treeData['connecting_G'], $pAll->treeData['connecting_B']);
                    $w = $box[4];
                    $h = $box[5];
                    imagefilledellipse($im,$w/2,$h/2,$w,$h,$outline);
                } else if ($type == "TitleType") {
                    if ($pAll->title_font['opaque_background']) {
                        $bg = imagecolorallocate($im, $pAll->title_font['background_R'], $pAll->title_font['background_G'], $pAll->title_font['background_B']);
                        imagefilledrectangle($im, 0, 0, $box[4], $box[5], $bg);
                    }
                    imagetextout($im, 0, 0, $pAll->title_font, $pAll->Title, false);
                }

                ob_start(); // Let's start output buffering.
                imagepng($im); //This will normally output the image, but because of ob_start(), it won't.
                $contents = ob_get_contents(); //Instead, output above is saved to $contents
                ob_end_clean(); //End the output buffer.
                $box[7] = base64_encode($contents);
                imagedestroy($im);
            }
        }
    }
}
