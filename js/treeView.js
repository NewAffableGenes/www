// 'canvas' variables
var canvas; // The canvas
var ctx;    // The canvas context
var w = 0;  // The canvas width
var h = 0;  // The canvas height

// Some operating variables
var report = '';
var timer;
var updateStarted = false;
var touches = [];
var lasttouches = [];
var numtouch = 0;
var touchmoved = false;
var singletouch = false;
var singletouchstart = 0;
var spx = 0;
var spy = 0;
var touchedrightitem = -1;

// When to show the next tip update           
var tipgap = 26;
var tipy = 18;
var tipfont = '16px courier';
var buttonwidth = 30;
var tiptime = 0;
var itip = 0;
var tipstr = "";
var tipstrings = [
    " Tips appear here...",
    " For a higher quality tree download the PDF",
    " You can use your mouse wheel to zoom in and out",
    " You can zoom in and out by pressing '+' or '-' on your keyboard",
    " Double click on a person to edit that person",
    " Double click on the marriage symbol ('=') to edit the family",
    " Double click on the title or the author (lower right corner) to edit options",
    " Right click a person or marriage symble ('=') to bring up more options",
    " Without permission to edit this tree you wont be able to use some functions",
    " Your choices of stacking siblings may be overridden in order to display spouses"
];

var numbimage = boxes.length;
var background_image = new Image();
var bimages = new Array(numbimage);
for (i = 0; i < numbimage; i++) {
    bimages[i] = new Image();
}

// Define const and var for right-click menu
const none = -1;
const indi = 0;
const fam = 1;
var righton = none;     // can be none, indi or fam
var rightBox = none;    // index of box which was right clicked
var rightx = 0;         // X posn of right menu (top left)
var righty = 0;         // Y posn of right menu (top left)
var righth = 10;        // Height of right menu
var rightw = 10;        // Width of right menu
var rightmenuitem = none; // The right click element being hovered above
// variables for right click menu
var rightgap = 2;
var rights = 24; // font size
var right_menu = [
    [["Edit data...", "/edit/edit_individual.php?i="],
    ["Make me the root", "/tree/make_root.php?i="],
    ["Do not show me", "/tree/dont_show.php?i="],
    ["Show at the top of a stack", "/tree/show_top.php?i="],
    ["Show below in stack", "/tree/show_below.php?i="]]
    ,
    [["Edit data...", "/edit/edit_family.php?f="]]
];

var hwidth = 0;
var hheight = 0;
var bottomgap = 5;
var mouseon = false;
var mousestart_x = 0;
var mousestart_y = 0;
var mouse_x = 0;
var mouse_y = 0;
var client_x = 0;
var client_y = 0;

function exit_view() {
    window.location.href = "/tree/tree.php";
}

function zoom_in() {
    zoom *= 1.189;
}

function zoom_out() {
    zoom /= 1.189;
}

window.document.addEventListener('keypress', function (e) {
    righton = none;
    var key = e.which || e.keyCode;
    if (key === 45) { // -
        zoom_out();
    } else if ((key === 43) || (key === 61)) { // + or =
        zoom_in();
    }
});

function getBox(sx, sy) {
    var x = (sx - hwidth / 2.0) / zoom + offset_x; // Get the horizontal coordinate
    var y = (sy - tipgap - hheight / 2.0) / zoom + offset_y; // Get the vertical coordinate
    var ret = -1;
    for (i = 0; i < boxes.length; i++) {
        if ((x >= boxes[i][2]) && (x <= boxes[i][2] + boxes[i][4]) && (
            y >= boxes[i][3]) && (y <= boxes[i][3] + boxes[i][5])) {
            ret = i;
        }
    }
    return ret;
}

document.addEventListener('dblclick', function (e) {
    righton = none;
    var box = getBox(e.clientX, e.clientY);
    if (box !== -1) {
        window.location.href = boxes[box][6];
    }
});

document.addEventListener('contextmenu', event => event.preventDefault());

document.addEventListener('mousedown', function (e) {
    if (e.which === 1) {
        if (righton !== none) {
            if (rightmenuitem !== -1) {
                window.location.href = right_menu[righton][rightmenuitem][1] + boxes[rightBox][1];
            }
            righton = none;
        } else {
            var x = (e.clientX - hwidth / 2.0) / zoom + offset_x; // Get the horizontal coordinate
            var y = (e.clientY - tipgap - hheight / 2.0) / zoom + offset_y; // Get the vertical coordinate
            if (e.clientY < tipgap) {
                if (e.clientX > w - 1 * buttonwidth)
                    exit_view();
                else if (e.clientX > w - 2 * buttonwidth)
                    zoom_out();
                else if (e.clientX > w - 3 * buttonwidth)
                    zoom_in();
            } else if ((x >= 0) && (x < uw) && (y >= 0) && (y < uh)) {
                mousestart_x = x;
                mousestart_y = y;
                mouseon = true;
            }
        }
    } else if (e.which === 3) {
        righton = none;
        // right button down
        rightx = e.clientX;
        righty = e.clientY - tipgap;
        rightBox = getBox(e.clientX, e.clientY);
        if (rightBox !== none) {
            if (boxes[rightBox][0] === 'individual') {
                righton = indi;
            } else if (boxes[rightBox][0] === 'family') {
                righton = fam;
            }
        }
    }
});

document.addEventListener('mouseup', function (e) {
    if (e.which == 1) {
        var x = (e.clientX - hwidth / 2.0) / zoom + offset_x; // Get the horizontal coordinate
        var y = (e.clientY - tipgap - hheight / 2.0) / zoom + offset_y; // Get the vertical coordinate
        if ((x >= 0) && (x < uw) && (y >= 0) && (y < uh)) {
            mouseon = false;
        }
    }
});

document.addEventListener('mousemove', function (e) {
    client_x = e.clientX;
    client_y = e.clientY - tipgap;
    var x = (client_x - hwidth / 2.0) / zoom + offset_x; // Get the horizontal coordinate
    var y = (client_y - hheight / 2.0) / zoom + offset_y; // Get the vertical coordinate
    mouse_x = x;
    mouse_y = y;
    if ((x < 0) || (x >= uw) || (y < 0) || (y >= uh)) {
        mouseon = false;
    }
    if (mouseon) {
        offset_x = mousestart_x - (e.clientX - hwidth / 2.0) / zoom;
        offset_y = mousestart_y - (e.clientY - tipgap - hheight / 2.0) / zoom;
    }
});

// Mouse wheel handler
document.addEventListener('wheel', function (e) {
    righton = none;
    var x = mouse_x;
    var y = mouse_y;
    if ((x >= 0) && (x < uw) && (y >= 0) && (y < uh)) {
        if ((e.deltaY < 0) && (zoom < 1.0)) {
            zoom_in();
            offset_x = x - (e.clientX - hwidth / 2.0) / zoom;
            offset_y = y - (e.clientY - tipgap - hheight / 2.0) / zoom;
        }
        if (e.deltaY > 0) {
            zoom_out();
            offset_x = x - (e.clientX - hwidth / 2.0) / zoom;
            offset_y = y - (e.clientY - tipgap - hheight / 2.0) / zoom;
        }
    }
});

// Code which is run every time the timer ticks
function update() {
    if (updateStarted) return;
    updateStarted = true;
    // Make a record of what time it is!
    var time = new Date();
    var now = time.getTime();
    // Check if the canvas size has changed
    var nw = window.innerWidth;
    var nh = window.innerHeight;
    if ((w != nw) || (h != nh)) {
        w = nw;
        h = nh;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        canvas.width = w;
        canvas.height = h;
    }
    hwidth = w; // htmlCanvas.width;
    hheight = h - tipgap - 1; // htmlCanvas.height;
    var htop = tipgap + 1;

    ctx.fillStyle = 'white';
    ctx.fillRect(0, htop, w, hheight);

    // What I want:
    // - Short (150ms) one finger tap on a zoom in/out or exit works
    // - Long (500ms) one finger hold on a box brings up the right click menu
    //   - Subsequent short tap on the right click menu does the action
    // - One finger hold and drag moves the display offset
    // - Two finger used to move and stretch the display

    if (numtouch !== touches.length) {
        if ((numtouch == 0) && (touches.length == 1)) { // Gone from no finger to 1 finger
            singletouch = true;
            touchmoved = false;
            singletouchstart = now;
            spx = touches[0].clientX;
            spy = touches[0].clientY;
        }
        if (singletouch && (touches.length == 0) && (now - singletouchstart < 250) && (now - singletouchstart > 50)) { // short tap dealt with here
            if (spy < tipgap) {
                if (spx > w - 1 * buttonwidth)
                    exit_view();
                else if (spx > w - 2 * buttonwidth)
                    zoom_out();
                else if (spx > w - 3 * buttonwidth)
                    zoom_in();
            }
            if (righton !== none) {
                var righth = right_menu[righton].length * (rights + rightgap) + rightgap;
                var rightw = 10;
                ctx.font = rights + "px Arial";
                for (i = 0; i < right_menu[righton].length; i++) {
                    rightw = Math.max(rightw, ctx.measureText(right_menu[righton][i][0]).width);
                }
                rightw += 2 * rightgap;
                rightx = Math.min(rightx, hwidth - rightw);
                righty = Math.min(righty, hheight - righth);
                touchedrightitem = -1;
                if ((spx > rightx) && (spx < rightx + rightw)) {
                    touchedrightitem = Math.floor((spy - rightgap - righty) / (rights + rightgap));
                    if ((touchedrightitem < 0) || (touchedrightitem >= right_menu[righton].length)) {
                        touchedrightitem = -1;
                    } 
                }
                report = touchedrightitem;
                if (touchedrightitem !== -1) {
                    window.location.href = right_menu[righton][touchedrightitem][1] + boxes[rightBox][1];
                }
            }
            righton = none;
        }
        numtouch = touches.length;
    } else {
        if (numtouch === 1) { // So there's 1 finger down and was last time too - Must be scrolling
            var dx = touches[0].clientX - lasttouches[0].clientX;
            var dy = touches[0].clientY - lasttouches[0].clientY;
            offset_x -= dx / zoom;
            offset_y -= dy / zoom;
        } else if (numtouch === 2) {// So there's 2 fingers down and was last time too - Must be scaling
            var ecx = (touches[0].clientX + lasttouches[0].clientX) / 2;
            var ecy = (touches[0].clientY + lasttouches[0].clientY) / 2;
            var x = (ecx - hwidth / 2.0) / zoom + offset_x; // Get the horizontal coordinate
            var y = (ecy - tipgap - hheight / 2.0) / zoom + offset_y; // Get the vertical coordinate
            zoom = zoom * Math.sqrt(
                ((touches[0].clientX - touches[1].clientX) * (touches[0].clientX - touches[1].clientX)
                    + (touches[0].clientY - touches[1].clientY) * (touches[0].clientY - touches[1].clientY)) /
                ((lasttouches[0].clientX - lasttouches[1].clientX) * (lasttouches[0].clientX - lasttouches[1].clientX)
                    + (lasttouches[0].clientY - lasttouches[1].clientY) * (lasttouches[0].clientY - lasttouches[1].clientY)));
            offset_x = x - (ecx - hwidth / 2.0) / zoom;
            offset_y = y - (ecy - tipgap - hheight / 2.0) / zoom;
            righton = none;
        }
    }
    if (numtouch !== 1) {
        singletouch = false;
    }
    if (singletouch && ((Math.abs(touches[0].clientX - spx) > 5) || (Math.abs(touches[0].clientY - spy) > 5))) {
        touchmoved = true;
        righton = none;
    }
    if (singletouch && !touchmoved && (now - singletouchstart > 1000)) { // Long single finger hold dealt with here
        righton = none;
        rightx = spx;
        righty = spy - tipgap;
        rightBox = getBox(spx, spy);
        if (rightBox !== none) {
            if (boxes[rightBox][0] === 'individual') {
                righton = indi;
            } else if (boxes[rightBox][0] === 'family') {
                righton = fam;
            }
        }
    }
    lasttouches = touches;

    // Now make sure all movement and zooming stays sensible
    zoom = Math.min(1.0, Math.max(Math.min(hwidth / uw, hheight / uh), zoom));
    if (hwidth / zoom > uw) {
        offset_x = uw / 2.0;
    } else {
        offset_x = Math.max(hwidth / 2.0 / zoom, offset_x);
        offset_x = Math.min(uw - hwidth / 2.0 / zoom, offset_x);
    }
    if (hheight / zoom > uh) {
        offset_y = uh / 2.0;
    } else {
        offset_y = Math.max(hheight / 2.0 / zoom, offset_y);
        offset_y = Math.min(uh - hheight / 2.0 / zoom, offset_y);
    }

    ctx.save();
    ctx.scale(zoom, zoom);

    ctx.translate(hwidth / 2.0 / zoom - offset_x, hheight / 2.0 / zoom - offset_y + htop / zoom);
    // Create a space
    ctx.fillStyle = 'white';
    // Create a clear space to draw the tree
    ctx.fillRect(0, 0, uw, uh);

    // Write the background image
    for (var x = 0; x < uw; x += bg_w) {
        for (var y = 0; y < uh; y += bg_h) {
            ctx.drawImage(background_image, x, y, Math.min(bg_w, uw - x), Math.min(bg_h, uh - y));
        }
    }

    // Draw the UI and border
    var sg = side_gap;
    var s2 = sg / 2.0;
    var ot = outline_thickness;
    var t2 = ot / 2.0;
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, uw, sg);
    ctx.fillRect(0, 0, sg, uh);
    ctx.fillRect(0, uh - sg, uw, sg);
    ctx.fillRect(uw - sg, 0, sg, uh);
    ctx.fillStyle = '#808080';
    ctx.fillRect(s2 - t2, s2 - t2, uw - sg + ot, ot);
    ctx.fillRect(s2 - t2, s2 - t2, ot, uh - sg + ot);
    ctx.fillRect(s2 - t2, uh - s2 - t2, uw - sg + ot, ot);
    ctx.fillRect(uw - s2 - t2, s2 - t2, ot, uh - sg + ot);
    t2 = line_thickness / 2.0;
    ctx.fillStyle = line_color;
    // Draw all the connecting lines
    for (i = 0; i < lines.length; i++) {
        if (lines[i][0] === 'v') {
            ctx.fillRect(lines[i][1] - t2, lines[i][2], 2 * t2, lines[i][3]);
        } else { // then it must be 'h'
            ctx.fillRect(lines[i][1], lines[i][2] - t2, lines[i][3], 2 * t2);
            ctx.beginPath();
            ctx.arc(lines[i][1], lines[i][2], t2, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();
            ctx.beginPath();
            ctx.arc(lines[i][1] + lines[i][3], lines[i][2], t2, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();
        }
    }

    for (i = 0; i < boxes.length; i++) {
        if (boxes[i][0] !== 'family') {
            ctx.drawImage(bimages[i], boxes[i][2], boxes[i][3], boxes[i][4], boxes[i][5]);
        }
    }

    ctx.restore();

    // display right click menu
    if ((righton !== none) && (rightBox !== none)) {
        var righth = right_menu[righton].length * (rights + rightgap) + rightgap;
        var rightw = 10;
        ctx.font = rights + "px Arial";
        for (i = 0; i < right_menu[righton].length; i++) {
            rightw = Math.max(rightw, ctx.measureText(right_menu[righton][i][0]).width);
        }
        rightw += 2 * rightgap;
        rightx = Math.min(rightx, hwidth - rightw);
        righty = Math.min(righty, hheight - righth);
        ctx.fillStyle = 'white';
        ctx.fillRect(rightx, righty, rightw, righth);
        rightmenuitem = -1;
        if ((client_x > rightx) && (client_x < rightx + rightw)) {
            rightmenuitem = Math.floor((client_y - rightgap - righty + tipgap) / (rights + rightgap));
            if ((rightmenuitem < 0) || (rightmenuitem >= right_menu[righton].length)) {
                rightmenuitem = -1;
            } else {
                ctx.fillStyle = 'lightgrey';
                ctx.fillRect(rightx + rightgap, righty + rightgap + rightmenuitem * (rights + rightgap), rightw - 2 * rightgap, rights + rightgap);
            }
        }
        ctx.strokeStyle = 'grey';
        ctx.lineWidth = rightgap;
        ctx.strokeRect(rightx, righty, rightw, righth);
        ctx.fillStyle = 'black';
        for (i = 0; i < right_menu[righton].length; i++) {
            ctx.fillText(right_menu[righton][i][0], rightx + rightgap, righty + (i + 1) * rights + i * rightgap);
        }
    }

    var i, len = touches.length;
    for (i = 0; i < len; i++) {
        var touch = touches[i];
        var px = touch.pageX;
        var py = touch.pageY;
        ctx.beginPath();
        ctx.arc(px, py, 20, 0, 2 * Math.PI, true);
        ctx.fillStyle = "rgba(0, 0, 200, 0.2)";
        ctx.fill();
        ctx.lineWidth = 2.0;
        ctx.strokeStyle = "rgba(0, 0, 200, 0.8)";
        ctx.stroke();
    }

    ctx.font = "12px Arial";
    ctx.fillStyle = 'black';
    ctx.fillText(report, 10, 50);

    // Now output the tips and buttons
    ctx.font = tipfont;
    if (now > tiptime) {
        ctx.fillStyle = 'lightgrey';
        ctx.fillRect(0, 0, w, tipgap);
        if (tipstr.length == 0) {
            tipstr = tipstrings[itip++];
            if (itip >= tipstrings.length) {
                itip = 0;
            }
            tiptime = now + 4000;
        } else {
            tipstr = tipstr.substring(1, tipstr.length);
            tiptime = now + 100;
        }
    }
    ctx.fillStyle = 'lightgrey';
    ctx.fillRect(0, 0, w, tipgap);
    ctx.fillStyle = 'black';
    ctx.fillText(tipstr, 1, tipy);

    if ((client_y < tipgap) && (client_x > w - 1 * buttonwidth) && (client_x < w - 0 * buttonwidth))
        ctx.fillStyle = 'darkgrey';
    else
        ctx.fillStyle = 'lightgrey';
    ctx.fillRect(w - 1 * buttonwidth, 0, buttonwidth, tipgap - 1);
    if ((client_y < tipgap) && (client_x > w - 2 * buttonwidth) && (client_x < w - 1 * buttonwidth))
        ctx.fillStyle = 'darkgrey';
    else
        ctx.fillStyle = 'lightgrey';
    ctx.fillRect(w - 2 * buttonwidth, 0, buttonwidth, tipgap - 1);
    if ((client_y < tipgap) && (client_x > w - 3 * buttonwidth) && (client_x < w - 2 * buttonwidth))
        ctx.fillStyle = 'darkgrey';
    else
        ctx.fillStyle = 'lightgrey';
    ctx.fillRect(w - 3 * buttonwidth, 0, buttonwidth, tipgap - 1);
    ctx.fillStyle = 'black';
    ctx.fillText('X', w - 0.7 * buttonwidth, tipy);
    ctx.fillText('-', w - 1.7 * buttonwidth, tipy);
    ctx.fillText('+', w - 2.7 * buttonwidth, tipy);
    ctx.strokeStyle = 'black';
    ctx.strokeRect(0, 0, w - 1, tipgap - 1);
    ctx.strokeRect(w - 1 * buttonwidth, 0, buttonwidth, tipgap - 1);
    ctx.strokeRect(w - 2 * buttonwidth, 0, buttonwidth, tipgap - 1);
    ctx.strokeRect(w - 3 * buttonwidth, 0, buttonwidth, tipgap - 1);

    updateStarted = false;
}

/////////////////////////////////////////////////////////////////////////
// This function is called once 'on load'
function ol() {
    canvas = document.getElementById('canvas');
    ctx = canvas.getContext('2d');

    for (i = 0; i < numbimage; i++) {
        bimages[i].src = "data:image/png;base64," + boxes[i][7];
    }
    background_image.src = "data:image/jpg;base64," + bg_image;
    var time = new Date();
    tiptime = time.getTime();

    timer = setInterval(update, 15);
    canvas.addEventListener('touchend', function () {
        event.preventDefault();
        touches = event.touches;
        // ctx.clearRect(0, 0, w, h);
    });

    canvas.addEventListener('touchmove', function (event) {
        event.preventDefault();
        touches = event.touches;
    });

    canvas.addEventListener('touchstart', function (event) {
        event.preventDefault();
        touches = event.touches;
        // console.log('start');
    });
};
