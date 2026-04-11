function getLikeCount(element) {
    const countSpan = element.querySelector('.like-count');
    if (!countSpan) return 0;

    const numero = (countSpan.textContent || '').replace(/[^\d]/g, '');
    return parseInt(numero, 10) || 0;
}

function applyLikeState(element, liked, count) {
    const icon = element.querySelector('i');
    const countSpan = element.querySelector('.like-count');

    element.dataset.liked = liked ? '1' : '0';

    element.classList.toggle('text-danger', liked);
    element.classList.toggle('is-liked', liked);
    element.classList.toggle('is-active', liked);

    if (icon) {
        icon.classList.toggle('bi-suit-heart-fill', liked);
        icon.classList.toggle('bi-suit-heart', !liked);
    }

    if (countSpan) {
        if (element.classList.contains('social-pill-like')) {
            countSpan.textContent = String(count);
        } else {
            countSpan.textContent = count > 0 ? ' ' + count : '';
        }
    }
}