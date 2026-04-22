// Shared UI helper for the PHP build
document.addEventListener("DOMContentLoaded", () => {
  const pageName = document.body.getAttribute("data-page");
  if (pageName) {
    document.querySelectorAll("[data-page-link]").forEach((link) => {
      if (link.getAttribute("data-page-link") === pageName) {
        link.classList.add("active-link");
      }
    });
  }

  document.querySelectorAll('form[data-confirm], button[data-confirm]').forEach((el) => {
    el.addEventListener('click', (event) => {
      const message = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  const newsSearch = document.getElementById('newsSearch');
  const newsButtons = document.querySelectorAll('[data-news-topic]');
  const newsItems = document.querySelectorAll('.news-item');
  const newsResultsText = document.getElementById('newsResultsText');
  const newsCount = document.getElementById('newsCount');
  const noNewsMessage = document.getElementById('noNewsMessage');
  const resetNews = document.getElementById('resetNewsFilters');
  if (newsItems.length) {
    let activeTopic = 'all';
    const filterNews = () => {
      const query = (newsSearch?.value || '').toLowerCase().trim();
      let visible = 0;
      newsItems.forEach((item) => {
        const topic = item.dataset.topic || '';
        const haystack = item.dataset.search || '';
        const matchTopic = activeTopic === 'all' || topic === activeTopic;
        const matchSearch = !query || haystack.includes(query);
        const show = matchTopic && matchSearch;
        item.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      if (newsResultsText) newsResultsText.textContent = `Showing ${visible} update${visible === 1 ? '' : 's'}`;
      if (newsCount) newsCount.textContent = String(visible);
      if (noNewsMessage) noNewsMessage.classList.toggle('d-none', visible !== 0);
    };
    newsButtons.forEach((btn) => btn.addEventListener('click', () => {
      activeTopic = btn.dataset.newsTopic || 'all';
      newsButtons.forEach((b) => b.classList.toggle('active', b === btn));
      filterNews();
    }));
    newsSearch?.addEventListener('input', filterNews);
    resetNews?.addEventListener('click', () => {
      activeTopic = 'all';
      if (newsSearch) newsSearch.value = '';
      newsButtons.forEach((b) => b.classList.toggle('active', (b.dataset.newsTopic || '') === 'all'));
      filterNews();
    });
  }

  const faqSearch = document.getElementById('faqSearch');
  const faqButtons = document.querySelectorAll('[data-faq-topic]');
  const faqItems = document.querySelectorAll('.faq-item');
  const faqResultsText = document.getElementById('faqResultsText');
  const noFaqMessage = document.getElementById('noFaqMessage');
  const resetFaq = document.getElementById('resetFaqFilters');
  if (faqItems.length) {
    let activeTopic = 'all';
    const filterFaq = () => {
      const query = (faqSearch?.value || '').toLowerCase().trim();
      let visible = 0;
      faqItems.forEach((item) => {
        const topic = item.dataset.faqTopic || '';
        const haystack = item.dataset.search || '';
        const show = (activeTopic === 'all' || topic === activeTopic) && (!query || haystack.includes(query));
        item.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      if (faqResultsText) faqResultsText.textContent = `Showing ${visible} question${visible === 1 ? '' : 's'}`;
      if (noFaqMessage) noFaqMessage.classList.toggle('d-none', visible !== 0);
    };
    faqButtons.forEach((btn) => btn.addEventListener('click', () => {
      activeTopic = btn.dataset.faqTopic || 'all';
      faqButtons.forEach((b) => b.classList.toggle('active', b === btn));
      filterFaq();
    }));
    faqSearch?.addEventListener('input', filterFaq);
    resetFaq?.addEventListener('click', () => {
      activeTopic = 'all';
      if (faqSearch) faqSearch.value = '';
      faqButtons.forEach((b) => b.classList.toggle('active', (b.dataset.faqTopic || '') === 'all'));
      filterFaq();
    });
  }

  const donateForm = document.getElementById('donateForm');
  const cardBlock = document.getElementById('cardDetailsBlock');
  if (donateForm && cardBlock) {
    const toggleCardBlock = () => {
      const method = donateForm.querySelector('input[name="payment_method"]:checked')?.value || 'card';
      const show = method === 'card';
      cardBlock.classList.toggle('d-none', !show);
      cardBlock.querySelectorAll('input').forEach((input) => input.required = show);
    };
    donateForm.querySelectorAll('input[name="payment_method"]').forEach((el) => el.addEventListener('change', toggleCardBlock));
    toggleCardBlock();
  }

  const aiForm = document.getElementById('aiChatForm');
  const aiInput = document.getElementById('aiChatInput');
  const aiMessages = document.getElementById('aiChatMessages');
  const aiStatus = document.getElementById('aiChatStatus');
  const clearAiChat = document.getElementById('clearAiChat');
  const promptButtons = document.querySelectorAll('.ai-prompt-chip');
  if (aiForm && aiInput && aiMessages) {
    const starter = aiMessages.innerHTML;
    const addMessage = (role, text) => {
      const wrap = document.createElement('div');
      wrap.className = `ai-message ${role === 'user' ? 'ai-message-user' : 'ai-message-assistant'}`;
      wrap.innerHTML = `<div class="ai-message-label">${role === 'user' ? 'You' : 'AI Guide'}</div><div class="ai-message-bubble"></div>`;
      wrap.querySelector('.ai-message-bubble').textContent = text;
      aiMessages.appendChild(wrap);
      aiMessages.scrollTop = aiMessages.scrollHeight;
    };
    const respond = (prompt) => {
      const text = prompt.toLowerCase();
      let reply = 'A good next step is to browse the Parks page by region, then compare Events and Map to narrow down the best destination.';
      if (text.includes('waterfall')) reply = 'For waterfall-focused trips, start with Letchworth State Park and Watkins Glen State Park. Both are strong picks for scenery, short hikes, and photography.';
      else if (text.includes('albany')) reply = 'Near Albany, consider Saratoga Spa State Park for families, trails, and easy planning. You can also compare parks on the Map page for distance.';
      else if (text.includes('finger lakes')) reply = 'A Finger Lakes weekend could combine Watkins Glen for scenic trails, a picnic stop, and a public event if one is listed on the Events page.';
      else if (text.includes('beach')) reply = 'For a Jones Beach outing, bring water, sunscreen, towels, a light layer, and check the weather before you go.';
      else if (text.includes('map') || text.includes('events') || text.includes('pages')) reply = 'Use Parks to browse destinations, Events for public programming, Map for location planning, FAQ for quick answers, and Donate if you want to support the system.';
      addMessage('assistant', reply);
      if (aiStatus) aiStatus.textContent = 'Demo response generated locally in the page. This can be replaced later with a live AI backend.';
    };
    aiForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const prompt = aiInput.value.trim();
      if (!prompt) return;
      addMessage('user', prompt);
      aiInput.value = '';
      window.setTimeout(() => respond(prompt), 250);
    });
    promptButtons.forEach((btn) => btn.addEventListener('click', () => {
      aiInput.value = btn.dataset.prompt || '';
      aiInput.focus();
    }));
    clearAiChat?.addEventListener('click', () => {
      aiMessages.innerHTML = starter;
      if (aiStatus) aiStatus.textContent = 'Tip: try one of the suggested prompts below to test the chatbot quickly.';
    });
  }
});
