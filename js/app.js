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
  const newsTopicFilter = document.getElementById('newsTopicFilter');
  const newsRegionFilter = document.getElementById('newsRegionFilter');
  const newsItems = document.querySelectorAll('.news-item');
  const newsResultsText = document.getElementById('newsResultsText');
  const newsCount = document.getElementById('newsCount');
  const noNewsMessage = document.getElementById('noNewsMessage');
  const resetNews = document.getElementById('resetNewsFilters');
  const resetNewsEmpty = document.getElementById('resetNewsFiltersEmpty');
  const applyNews = document.getElementById('applyNewsFilters');
  if (newsItems.length) {
    let activeTopic = newsTopicFilter?.value || 'all';
    let activeRegion = newsRegionFilter?.value || 'all';
    const filterNews = () => {
      activeTopic = newsTopicFilter?.value || activeTopic || 'all';
      activeRegion = newsRegionFilter?.value || activeRegion || 'all';
      const query = (newsSearch?.value || '').toLowerCase().trim();
      let visible = 0;
      newsItems.forEach((item) => {
        const topic = item.dataset.topic || '';
        const haystack = item.dataset.search || '';
        const region = item.dataset.region || '';
        const matchTopic = activeTopic === 'all' || topic === activeTopic;
        const matchRegion = activeRegion === 'all' || region === activeRegion;
        const matchSearch = !query || haystack.includes(query);
        const show = matchTopic && matchRegion && matchSearch;
        item.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      if (newsResultsText) newsResultsText.textContent = `Showing ${visible} update${visible === 1 ? '' : 's'}`;
      if (newsCount) newsCount.textContent = String(visible);
      if (noNewsMessage) noNewsMessage.classList.toggle('d-none', visible !== 0);
    };
    newsButtons.forEach((btn) => btn.addEventListener('click', () => {
      activeTopic = btn.dataset.newsTopic || 'all';
      if (newsTopicFilter) newsTopicFilter.value = activeTopic;
      newsButtons.forEach((b) => b.classList.toggle('active', b === btn));
      filterNews();
    }));
    newsSearch?.addEventListener('input', filterNews);
    newsTopicFilter?.addEventListener('change', filterNews);
    newsRegionFilter?.addEventListener('change', filterNews);
    applyNews?.addEventListener('click', filterNews);
    document.querySelectorAll('.news-open-article').forEach((btn) => btn.addEventListener('click', () => {
      const content = btn.closest('.news-card')?.querySelector('.news-article-content');
      if (!content) return;
      const isOpen = content.classList.toggle('show');
      btn.textContent = isOpen ? 'Close article' : 'Open article';
    }));
    const resetNewsFilters = () => {
      activeTopic = 'all';
      activeRegion = 'all';
      if (newsSearch) newsSearch.value = '';
      if (newsTopicFilter) newsTopicFilter.value = 'all';
      if (newsRegionFilter) newsRegionFilter.value = 'all';
      newsButtons.forEach((b) => b.classList.toggle('active', (b.dataset.newsTopic || '') === 'all'));
      filterNews();
    };
    resetNews?.addEventListener('click', resetNewsFilters);
    resetNewsEmpty?.addEventListener('click', resetNewsFilters);
    filterNews();
  }

  const faqSearch = document.getElementById('faqSearch');
  const faqButtons = document.querySelectorAll('.faq-topic-button[data-faq-topic]');
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
    const chatHistory = [];
    const submitButton = aiForm.querySelector('button[type="submit"]');

    const addMessage = (role, text, save = true) => {
      const wrap = document.createElement('div');
      wrap.className = `ai-message ${role === 'user' ? 'ai-message-user' : 'ai-message-assistant'}`;
      wrap.innerHTML = `<div class="ai-message-label">${role === 'user' ? 'You' : 'AI Guide'}</div><div class="ai-message-bubble"></div>`;
      wrap.querySelector('.ai-message-bubble').textContent = text;
      aiMessages.appendChild(wrap);
      aiMessages.scrollTop = aiMessages.scrollHeight;
      if (save) chatHistory.push({ role, text });
    };

    const setLoading = (loading) => {
      if (submitButton) {
        submitButton.disabled = loading;
        submitButton.innerHTML = loading ? '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Sending' : '<i class="bi bi-send-fill"></i> Send';
      }
      aiInput.disabled = loading;
    };

    const requestAiReply = async (prompt) => {
      setLoading(true);
      if (aiStatus) aiStatus.textContent = 'Sending your question to the NYS Parks AI Guide...';
      try {
        const response = await fetch('ai-api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: prompt, history: chatHistory.slice(-6) })
        });
        const data = await response.json();
        if (!data.ok) {
          throw new Error(data.error || 'The AI Guide could not answer right now.');
        }
        addMessage('assistant', data.reply || 'I could not generate a response this time. Please try again.');
        if (aiStatus) aiStatus.textContent = 'Live response generated through ai-api.php.';
      } catch (error) {
        addMessage('assistant', error.message || 'The AI Guide could not answer right now. Please try again later.');
        if (aiStatus) aiStatus.textContent = 'The chatbot request did not complete. Check ai-api.php and your OpenAI API key.';
      } finally {
        setLoading(false);
        aiInput.focus();
      }
    };

    aiForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const prompt = aiInput.value.trim();
      if (!prompt) return;
      addMessage('user', prompt);
      aiInput.value = '';
      requestAiReply(prompt);
    });
    promptButtons.forEach((btn) => btn.addEventListener('click', () => {
      aiInput.value = btn.dataset.prompt || '';
      aiInput.focus();
    }));
    clearAiChat?.addEventListener('click', () => {
      aiMessages.innerHTML = starter;
      chatHistory.length = 0;
      if (aiStatus) aiStatus.textContent = 'Tip: try one of the suggested prompts below to test the chatbot quickly.';
    });
  }
});
