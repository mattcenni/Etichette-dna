document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('[data-page]');
    const pageContents = document.querySelectorAll('.page-content');
    const pageTitle = document.getElementById('page-title');

    console.log('Numero di nav-links trovati:', navLinks.length);
    console.log('Nav links:', navLinks);

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            
            
            // Gestione contenuti
            pageContents.forEach(content => content.classList.add('hidden'));
            const pageId = this.getAttribute('data-page');
            document.getElementById(pageId + '-content')?.classList.remove('hidden');
            
            pageTitle.textContent = this.textContent;
        });
    });
}); 