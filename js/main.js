var slideIndex = 1;
showSlides(slideIndex);

// Next/previous controls
function plusSlides(n) {
    showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    var i;
    var slides = document.getElementsByClassName("mySlides");
    var dots = document.getElementsByClassName("demo");

    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }

    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }

    if (slides[slideIndex - 1] && dots[slideIndex - 1]) {
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
    } 
    if (dots.length > 5) {
        for (i = 0; i < dots.length; i++) {
            dots[i].style.display = "none";
        }

        if (slideIndex > 2 && slideIndex < dots.length - 1) {
            for (i = 0; i < 5; i++) {
                dots[slideIndex - 3 + i].style.display = "block";
            }
        } else if (slideIndex < 3) {
            for (i = 0; i < 5; i++) {
                dots[i].style.display = "block";
            }
        } else if (slideIndex > dots.length - 2) {
            for (i = dots.length - 1; i > dots.length - 6; i--) {
                dots[i].style.display = "block";
            }
        }
    }
}
