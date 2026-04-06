
async function toggleLike(element, idRef) {
    if (!element || !idRef) return;
    if (element.dataset.loading === '1') return;

    const likedAtual =
        element.dataset.liked === '1' ||
        element.classList.contains('is-active') ||
        element.classList.contains('is-liked') ||
        element.classList.contains('text-danger');

    const countAtual = getLikeCount(element);
    const likedNovo = !likedAtual;
    const countNovo = likedNovo
        ? (countAtual + 1)
        : Math.max(0, countAtual - 1);

    applyLikeState(element, likedNovo, countNovo);
    element.dataset.loading = '1';

    try {
        const formData = new FormData();
        formData.append('acao', 'toggle_like');
        formData.append('id_ref', idRef);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Falha ao atualizar like.');
        }

        applyLikeState(element, !!data.liked, parseInt(data.count, 10) || 0);

    } catch (error) {
        applyLikeState(element, likedAtual, countAtual);
        console.error('Erro no like:', error);
    } finally {
        element.dataset.loading = '0';
    }
}