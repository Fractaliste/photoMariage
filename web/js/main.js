$('.menu').on('click', 'a', function (e) {
    if (!$(e.target).hasClass('bubble'))
        e.preventDefault()
});