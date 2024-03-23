<?php

class OutputMap
{
    private $slices; // A list of all slices (OutputMapSlice)
    function __construct()
    {
        // 'Create the default map which is a 3x1 of gaps with a single seed at the middle
        // for the Sibling Spouse group of the root person
        $this->slices = [new OutputMapSlice(SeedSlice)];
        $this->slices[0]->Complete = false; // Mark this slice for expansion
        $this->GrowColumnToLeftOf(0);
        $this->GrowColumnToRightOf(0);
    }
    function ShowStructure($Title)
    {
        // Temporary code to put map into a string to view it on debug
        // Uncomment this section if you want to see the structure as it grows
        $enabled = false;
        if ($enabled) {
            echo "$Title<br>";
            $nextRow = $this->TopLeft();
            while ($nextRow !== null) {
                $pSlice = $nextRow;
                $nextRow = $this->slices[$nextRow]->Below;
                while ($pSlice !== null) {
                    switch ($this->slices[$pSlice]->Type) {
                        case UpSlice:
                            echo 'U';
                            break;
                        case DnSlice:
                            echo 'D';
                            break;
                        case ContSlice:
                            echo '=';
                            break;
                        case GapSlice:
                            echo '.';
                            break;
                        case SeedSlice:
                            echo 'S';
                            break;
                        default:
                            echo 'X';
                            break;
                    }
                    // echo '(' . $this->slices[$pSlice]->SSGroup . ')';
                    $pSlice = $this->slices[$pSlice]->Right;
                }
                echo '<br>';
            }
        }
    }
    function Left($pSlice)
    {
        return $this->slices[$pSlice]->Left;
    }
    function Right($pSlice)
    {
        return $this->slices[$pSlice]->Right;
    }
    function Above($pSlice)
    {
        return $this->slices[$pSlice]->Above;
    }
    function Below($pSlice)
    {
        return $this->slices[$pSlice]->Below;
    }
    function Type($pSlice)
    {
        return $this->slices[$pSlice]->Type;
    }
    function SSGroup($pSlice)
    {
        return $this->slices[$pSlice]->SSGroup;
    }
    function isComplete($pSlice)
    {
        return $this->slices[$pSlice]->Complete;
    }
    function setComplete($pSlice, $b)
    {
        $this->slices[$pSlice]->Complete = $b;
    }
    function setSSGroup($pSlice, $b)
    {
        $this->slices[$pSlice]->SSGroup = $b;
    }
    function setType($pSlice, $b)
    {
        $this->slices[$pSlice]->Type = $b;
    }
    function TopLeft()
    {
        $pSlice = 0;
        while ($this->slices[$pSlice]->Above !== null) {
            $pSlice = $this->slices[$pSlice]->Above;
        }
        while ($this->slices[$pSlice]->Left !== null) {
            $pSlice = $this->slices[$pSlice]->Left;
        }
        return $pSlice;
    }
    function BottomLeft()
    {
        $pSlice = 0;
        while ($this->slices[$pSlice]->Below !== null) {
            $pSlice = $this->slices[$pSlice]->Below;
        }
        while ($this->slices[$pSlice]->Left !== null) {
            $pSlice = $this->slices[$pSlice]->Left;
        }
        return $pSlice;
    }
    function GrowColumnToLeftOf($InSlice)
    {
        $pSlice = $InSlice;
        while ($this->slices[$pSlice]->Above !== null) {
            $pSlice = $this->slices[$pSlice]->Above;
        }
        while ($pSlice !== null) {
            $thisType = $this->slices[$pSlice]->Type;
            $adjSlice = $this->slices[$pSlice]->Left;
            if ($adjSlice === null) {
                $adjType = GapSlice;
            } else {
                $adjType = $this->slices[$adjSlice]->Type;
            }
            if (($adjType == GapSlice) || ($thisType == GapSlice)) {
                $newType = GapSlice;
            } else {
                $newType = ContSlice;
            }
            $newSlice = count($this->slices);
            array_push($this->slices, new OutputMapSlice($newType));
            $this->slices[$pSlice]->Left = $newSlice;
            $this->slices[$newSlice]->Right = $pSlice;
            if ($adjSlice !== null) {
                $this->slices[$adjSlice]->Right = $newSlice;
            }
            $this->slices[$newSlice]->Left = $adjSlice;
            if ($this->slices[$pSlice]->Above !== null) {
                // pSlice.Above.Left.Below = $newSlice;
                $this->slices[$this->slices[$this->slices[$pSlice]->Above]->Left]->Below = $newSlice;
                // pSlice.Left.Above = pSlice.Above.Left;
                $this->slices[$newSlice]->Above = $this->slices[$this->slices[$pSlice]->Above]->Left;
            }
            $pSlice = $this->slices[$pSlice]->Below;
        }
    }
    function GrowColumnToRightOf($InSlice)
    {
        $pSlice = $InSlice;
        while ($this->slices[$pSlice]->Above !== null) {
            $pSlice = $this->slices[$pSlice]->Above;
        }
        while ($pSlice !== null) {
            $thisType = $this->slices[$pSlice]->Type;
            $adjSlice = $this->slices[$pSlice]->Right;
            if ($adjSlice === null) {
                $adjType = GapSlice;
            } else {
                $adjType = $this->slices[$adjSlice]->Type;
            }
            if (($adjType == GapSlice) || ($thisType == GapSlice)) {
                $newType = GapSlice;
            } else {
                $newType = ContSlice;
            }
            $newSlice = count($this->slices);
            array_push($this->slices, new OutputMapSlice($newType));
            $this->slices[$pSlice]->Right = $newSlice;
            $this->slices[$newSlice]->Left = $pSlice;
            if ($adjSlice !== null) {
                $this->slices[$adjSlice]->Left = $newSlice;
            }
            $this->slices[$newSlice]->Right = $adjSlice;
            if ($this->slices[$pSlice]->Above !== null) {
                // pSlice.Above.Left.Below = $newSlice;
                $this->slices[$this->slices[$this->slices[$pSlice]->Above]->Right]->Below = $newSlice;
                // pSlice.Left.Above = pSlice.Above.Left;
                $this->slices[$newSlice]->Above = $this->slices[$this->slices[$pSlice]->Above]->Right;
            }
            $pSlice = $this->slices[$pSlice]->Below;
        }
    }
    function GrowRowOfGapsAtTop()
    {
        $pSlice = $this->TopLeft();
        while ($pSlice !== null) {
            $newSlice = count($this->slices);
            array_push($this->slices, new OutputMapSlice(GapSlice));
            $this->slices[$pSlice]->Above = $newSlice;
            $this->slices[$newSlice]->Below = $pSlice;
            if ($this->slices[$pSlice]->Left !== null) {
                $this->slices[$newSlice]->Left = $this->slices[$this->slices[$pSlice]->Left]->Above;
                $this->slices[$this->slices[$this->slices[$pSlice]->Left]->Above]->Right = $newSlice;
            }
            $pSlice = $this->slices[$pSlice]->Right;
        }
    }
    function GrowRowOfGapsAtBottom()
    {
        $pSlice = $this->BottomLeft();
        while ($pSlice !== null) {
            $newSlice = count($this->slices);
            array_push($this->slices, new OutputMapSlice(GapSlice));
            $this->slices[$pSlice]->Below = $newSlice;
            $this->slices[$newSlice]->Above = $pSlice;
            if ($this->slices[$pSlice]->Left !== null) {
                $this->slices[$newSlice]->Left = $this->slices[$this->slices[$pSlice]->Left]->Below;
                $this->slices[$this->slices[$this->slices[$pSlice]->Left]->Below]->Right = $newSlice;
            }
            $pSlice = $this->slices[$pSlice]->Right;
        }
    }
    function createUpLink($pSlice, $SSO, $index, $ifam, $comp)
    {
        // Make sure there is a row to put the corresponding down link
        if ($this->slices[$pSlice]->Above === null) {
            $this->GrowRowOfGapsAtTop();
        }
        $this->slices[$pSlice]->Type = UpSlice;
        $this->slices[$pSlice]->SSGroup = $SSO;
        $this->slices[$pSlice]->pUpLink = $index;
        if ($ifam !== null) {
            $this->slices[$this->slices[$pSlice]->Above]->Type = DnSlice;
            $this->slices[$this->slices[$pSlice]->Above]->Complete = $comp;
        }
        $this->slices[$pSlice]->Complete = true;
    }
    function createDnLink($pSlice, $SSO, $index, $fam, $comp)
    {
        if ($this->slices[$pSlice]->Below === null) {
            $this->GrowRowOfGapsAtBottom();
        }
        $this->slices[$pSlice]->Type = DnSlice;
        $this->slices[$pSlice]->pDnLink = $index;
        $this->slices[$pSlice]->SSGroup = $SSO;
        if ($fam !== null) {
            $this->slices[$this->slices[$pSlice]->Below]->Type = UpSlice;
            $this->slices[$this->slices[$pSlice]->Below]->Complete = $comp;
        }
        $this->slices[$pSlice]->Complete = true;
    }
    function pUpLink($iSlice)
    {
        return $this->slices[$iSlice]->pUpLink;
    }
    function pDnLink($iSlice)
    {
        return $this->slices[$iSlice]->pDnLink;
    }
    function TypeBelow($iSlice)
    {
        return $this->slices[$this->slices[$iSlice]->Below]->Type;
    }
    function TypeLeft($iSlice)
    {
        return $this->slices[$this->slices[$iSlice]->Left]->Type;
    }
    function TypeRight($iSlice)
    {
        return $this->slices[$this->slices[$iSlice]->Right]->Type;
    }
    function setTypeLeft($iSlice,$t)
    {
        $this->slices[$this->slices[$iSlice]->Left]->Type = $t;
    }
    function setTypeRight($iSlice,$t)
    {
        $this->slices[$this->slices[$iSlice]->Right]->Type = $t;
    }
    function TypeAbove($iSlice)
    {
        return $this->slices[$this->slices[$iSlice]->Above]->Type;
    }
    function countGenerations() {
        $nGen = 0;
        $nextRow = $this->TopLeft();
        while ($nextRow !== null) {
            $nGen++;
            $nextRow = $this->slices[$nextRow]->Below;
        }
        return $nGen;
    }
}
