function zoomImage(img) {
    if (img.style.transform === 'scale(1.5)') {
        img.style.transform = 'scale(1)';
    } else {
        img.style.transform = 'scale(1.5)';
    }
}
