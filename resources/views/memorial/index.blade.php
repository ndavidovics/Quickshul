<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Memorial Board</title>
<style>.yarmem { border: 7px solid yellow !important; }</style>
<link rel="stylesheet" type="text/css" href="/memorial/assets/screen.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>document.addEventListener('DOMContentLoaded', function() { document.body.style.zoom = window.innerHeight / 1080; });</script>
</head>
<body>
<div id="main_box">

<div id="left_candle" style="background-image:url(/memorial/assets/candle.gif);
    background-size:cover;
    color:#E8CB8A;
    display:flex;
    font-size:40px;
    justify-content:center;
    align-items:flex-end;
    direction:rtl;
    font-family:'WE Siddur Reg';">
    {{ $mishnaYomi }}
</div>

<div id="right_side"></div>

</div>

<script>
(function () {
    const slideCount = {{ $slideCount }};
    const slideDuration = 8000;
    let currentSlide = 1;
    let timer = null;

    function loadSlide(slideNumber) {
        const url = '/memorial-board/slide/' + slideNumber;

        $('#right_side').load(url, function (response, status) {
            if (status !== 'success') {
                console.error('Failed to load slide:', url);
            }

            timer = setTimeout(function () {
                currentSlide++;

                if (currentSlide > slideCount) {
                    currentSlide = 1;
                }

                loadSlide(currentSlide);
            }, slideDuration);
        });
    }

    $(document).ready(function () {
        loadSlide(currentSlide);
    });
})();
</script>
</body>
</html>
