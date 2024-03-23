<?php

define('GapSlice', 0);
define('SeedSlice', 1);
define('ContSlice', 2);
define('UpSlice', 3);
define('DnSlice', 4);

class OutputMapSlice {

    var $Left;      // Index in $this->slices of the slice to the left
    var $Right;     // Index in $this->slices of the slice to the right
    var $Above;     // Index in $this->slices of the slice above
    var $Below;     // Index in $this->slices of the slice below
    var $Complete;  // False implies that this is a seed which needs expanding
    var $pUpLink;   // The SSGroup has an array apUpLink and this is the index into that array for this slice
                    // elements of the apUpLink are; MaxX, MinX, LinkedFamily and Completed
    var $pDnLink;   // The SSGroup has an array apDnLink and this is the index into that array for this slice
                    // elements of the apDnLink are; Px, Py, LinkedFamily and Completed
    var $SSGroup;   // SSObject occupying this (and adjacent slices)
    var $Type;      // Type of slice. String: UpLinkType, DnLinkType, Seed, Gap or Cont

    /** Creates a new instance of OutputMapSlice */

    function __construct($TypeIn) {
        $this->Left = null;
        $this->Right = null;
        $this->Above = null;
        $this->Below = null;
        $this->Complete = true;
        $this->pUpLink = null;
        $this->pDnLink = null;
        $this->SSGroup = null;
        $this->Type = $TypeIn;
    }

}
