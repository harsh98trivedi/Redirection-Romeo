document.addEventListener('DOMContentLoaded', () => {
    
    // DOM Elements
    const dom = {
        btnNew: document.getElementById('rr-btn-new'),
        panel: document.getElementById('rr-creator-panel'),
        btnCancel: document.getElementById('rr-cancel'),
        form: document.getElementById('rr-form'),
        targetType: document.getElementById('rr-target-type'),
        groupUrl: document.getElementById('rr-group-url'),
        groupPost: document.getElementById('rr-group-post'),
        postSearchInput: document.getElementById('rr-post-search-input'),
        searchResults: document.getElementById('rr-search-results'),
        targetPostId: document.getElementById('rr-target-post-id'),
        selectedPost: document.getElementById('rr-selected-post'),
        modalTitle: document.getElementById('rr-modal-title'),
        btnSave: document.getElementById('rr-save-btn')
    };

    let isEditing = false;
    let editId = null;

    // Toggle Panel
    dom.btnNew.addEventListener('click', () => {
        resetForm();
        dom.panel.classList.remove('hidden');
        dom.modalTitle.textContent = 'Create New Redirect';
        dom.btnSave.textContent = 'Save Redirect';
    });

    dom.btnCancel.addEventListener('click', () => {
        dom.panel.classList.add('hidden');
        resetForm();
    });

    function resetForm() {
        dom.form.reset();
        isEditing = false;
        editId = null;
        dom.targetType.value = 'url';
        dom.targetType.dispatchEvent(new Event('change'));
        
        // Reset Search
        const cardSearch = document.getElementById('rr-card-search');
        if(cardSearch) cardSearch.value = '';

        dom.targetPostId.value = '';
        dom.selectedPost.classList.add('hidden');
        dom.postSearchInput.classList.remove('hidden');
        dom.searchResults.innerHTML = '';
        dom.searchResults.classList.add('hidden');
    }

    // Toggle Types
    dom.targetType.addEventListener('change', (e) => {
        if(e.target.value === 'url') {
            dom.groupUrl.classList.remove('hidden');
            dom.groupPost.classList.add('hidden');
            document.querySelector('[name="target_url"]').setAttribute('required', 'required');
            dom.targetPostId.removeAttribute('required');
        } else {
            dom.groupUrl.classList.add('hidden');
            dom.groupPost.classList.remove('hidden');
            document.querySelector('[name="target_url"]').removeAttribute('required');
        }
    });

    // Card Search (Client Side) - Enhanced
    const cardSearch = document.getElementById('rr-card-search');
    const cardGrid = document.getElementById('rr-card-grid');

    if(cardSearch) {
        cardSearch.addEventListener('keyup', (e) => {
            const term = e.target.value.toLowerCase();
            const cards = cardGrid.getElementsByClassName('rr-redirect-card');
            
            Array.from(cards).forEach(card => {
                const slug = card.getAttribute('data-slug') || '';
                const target = card.getAttribute('data-target') || '';
                
                if (slug.includes(term) || target.includes(term)) {
                    card.style.display = '';
                    card.style.opacity = '1';
                } else {
                    card.style.display = 'none';
                    card.style.opacity = '0';
                }
            });
        });
    }

    // Internal Post Search (Debounced)
    let searchTimeout = null;
    dom.postSearchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const term = e.target.value;
        if(term.length < 2) {
            dom.searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(ajaxurl + '?action=rr_search_posts&nonce=' + rr_vars.nonce + '&term=' + term)
            .then(res => res.json())
            .then(data => {
                dom.searchResults.innerHTML = '';
                if(data.success && data.data.length > 0) {
                    data.data.forEach(post => {
                        const div = document.createElement('div');
                        div.className = 'rr-result-item';
                        div.innerHTML = `<span>${post.title}</span> <span class="rr-badge" style="background:#f1f5f9">${post.type}</span>`;
                        div.onclick = () => selectPost(post);
                        dom.searchResults.appendChild(div);
                    });
                    dom.searchResults.classList.remove('hidden');
                } else {
                    dom.searchResults.innerHTML = '<div style="padding:10px;text-align:center;color:#666">No results found</div>';
                    dom.searchResults.classList.remove('hidden');
                }
            });
        }, 300);
    });

    function selectPost(post) {
        dom.targetPostId.value = post.id;
        dom.selectedPost.querySelector('.text').textContent = post.title;
        dom.selectedPost.classList.remove('hidden');
        dom.postSearchInput.classList.add('hidden');
        dom.searchResults.classList.add('hidden');
    }

    document.querySelector('.rr-remove-selection').addEventListener('click', () => {
        dom.targetPostId.value = '';
        dom.selectedPost.classList.add('hidden');
        dom.postSearchInput.classList.remove('hidden');
        dom.postSearchInput.value = '';
        dom.postSearchInput.focus();
    });

    // Save Form
    dom.form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(dom.form);
        formData.append('action', 'rr_save_redirect');
        formData.append('nonce', rr_vars.nonce);
        
        if (isEditing && editId) {
            formData.append('id', editId);
        }

        // Validation for internal posts
        if(dom.targetType.value === 'post' && !dom.targetPostId.value) {
            alert('Please select a post');
            return;
        }

        const originalText = dom.btnSave.textContent;
        dom.btnSave.textContent = 'Saving...';
        dom.btnSave.disabled = true;

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                location.reload();
            } else {
                alert(res.data);
                dom.btnSave.textContent = originalText;
                dom.btnSave.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred');
            dom.btnSave.textContent = originalText;
            dom.btnSave.disabled = false;
        });
    });

    // Global Actions (Edit/Delete)
    window.rrDelete = function(id) {
        if(!confirm('Are you sure you want to delete this redirect?')) return;
        
        const card = document.getElementById('card-' + id);
        // Optimistic UI interaction
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('action', 'rr_delete_redirect');
        formData.append('id', id);
        formData.append('nonce', rr_vars.delete_nonce);

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                card.style.transform = 'scale(0.9)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            } else {
                alert('Error deleting');
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        });
    }

    window.rrEdit = function(data) {
        // console.log('Editing', data); // Removed for production
        
        // Open Panel
        dom.panel.classList.remove('hidden');
        dom.modalTitle.textContent = 'Edit Redirect';
        dom.btnSave.textContent = 'Update Redirect';
        isEditing = true;
        editId = data.id;

        // Populate Form
        dom.form.querySelector('[name="slug"]').value = data.slug;
        dom.form.querySelector('[name="code"]').value = data.code;
        
        dom.targetType.value = data.type;
        // Trigger change to toggle views
        dom.targetType.dispatchEvent(new Event('change'));

        if(data.type === 'url') {
            dom.form.querySelector('[name="target_url"]').value = data.target;
        } else {
            if (data.target_title) {
                dom.targetPostId.value = data.target;
                dom.selectedPost.querySelector('.text').textContent = data.target_title;
                dom.selectedPost.classList.remove('hidden');
                dom.postSearchInput.classList.add('hidden');
            } else {
                 // Fallback if title missing (deleted post)
                 dom.targetPostId.value = data.target;
                 dom.selectedPost.querySelector('.text').textContent = "Post #" + data.target;
                 dom.selectedPost.classList.remove('hidden');
                 dom.postSearchInput.classList.add('hidden');
            }
        }
    }
});
