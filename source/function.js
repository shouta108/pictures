function batsu() {
    var batsu = document.getElementById("clear");
    var text = document.getElementById("textbox").value;
    if (text != "") {
        batsu.style.visibility = "visible"; 
    } else {
        batsu.style.visibility = "hidden";
    }
}

function not_enter_check(id) {
    var text = document.getElementById(id);
    if (text.value == "") {
        return false;
    }
}

function searchclear() {
    document.getElementById("textbox").value= "";
    document.getElementById("clear").style.visibility = "hidden";
}

function gotop() {
    location.href = "./top.php";
}

//ポップアップのidを入れる
function confirm_pop(pop) {
    popup(true, pop);
    return false;
}
//fromのnameを入れる
function okfunc(form) {
    form.submit();
}
//ポップアップのidを入れる
function nofunc(pop) {
    popup(false, pop);
}
function popup(e, popup) {
    var pop = document.getElementById(popup);
    var fade = document.getElementById("fadeLayer");
    if (e) {
        pop.style.visibility = "visible";
        fade.style.visibility = "visible";
    } else {
        pop.style.visibility = "hidden";
        fade.style.visibility = "hidden";
    }
}

function background_color_change(colorId , page, back1, back2) {
    let image = document.getElementById(back1);
    let enlarge = document.getElementById(back2);
    let color = "";

    switch (colorId) {
        case 1:
            color = "var(--back-color-1)";
            break;
        case 2:
            color = "var(--back-color-2)";
            break;
    }
    image.style.backgroundColor = color;
    if (typeof back2 !== "undefined") {
        enlarge.style.backgroundColor = color;
    }
    document.cookie = page + "_colorId=" + colorId + "; max-age=3600";
}

function radio(num, left, right) {
    let num1 = document.getElementById(left);
    let num2 = document.getElementById(right);
    if (num == 1) {
        num1.style.backgroundColor = "#cccccc";
        num1.style.transform = "translateY(var(--button-depth))";
        num1.style.boxShadow = "var(--button-shadow-1)";
        num1.style.transition = "0.5s";
        num2.style.backgroundColor = "var(--back-color-2)";
        num2.style.transform = "translateY(0px)";
        num2.style.boxShadow = "var(--button-shadow-2)";
        num2.style.transition = "0.5s";
    } else {
        num1.style.backgroundColor = "var(--back-color-1)";
        num1.style.transform = "translateY(0px)";
        num1.style.boxShadow = "var(--button-shadow-2)";
        num1.style.transition = "0.5s";
        num2.style.backgroundColor = "#313131";
        num2.style.transform = "translateY(var(--button-depth))";
        num2.style.boxShadow = "var(--button-shadow-1)";
        num2.style.transition = "0.5s";
    }
}

function submit_once(button, form_id) {
    button.disabled = true;
    let f = document.getElementById(form_id);
    f.submit();
}

