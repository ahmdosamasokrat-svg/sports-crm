/**
 * SokratCRM Unified Custom Dropdown Engine
 * Automatically upgrades <select class="crm-custom-select"> or <select data-crm-dropdown>
 * into a fully themed, accessible, searchable, custom dropdown with single and multiselect support.
 */

(function () {
  'use strict';

  const SVG_CHEVRON = `<svg class="crm-dropdown-chevron" width="11" height="11" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>`;
  const SVG_CHECK = `<svg class="crm-dropdown-check" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/></svg>`;
  const SVG_SEARCH = `<svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>`;

  function buildCustomDropdown(select) {
    if (select.__crmDropdownActive) return;
    select.__crmDropdownActive = true;

    const isMultiple = select.multiple;
    const iconSvg = select.dataset.icon || '';
    const searchable = select.options.length > 5;
    const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';
    const searchPlaceholder = isRtl ? 'بحث...' : 'Search...';
    const noResultsText = isRtl ? 'لا توجد نتائج مطابقة' : 'No matching results';
    const allOptionText = isMultiple ? (select.dataset.placeholder || (isRtl ? 'الكل' : 'All')) : '';

    // Create wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'crm-dropdown' + (isMultiple ? ' is-multiselect' : '');
    if (select.className) {
      wrapper.className += ' ' + select.className.replace(/crm-custom-select/g, '').replace(/filter-control/g, '').trim();
    }
    if (select.id) {
      wrapper.dataset.selectId = select.id;
    }

    // Hide original select visually but keep it in DOM for forms/submits
    select.style.position = 'absolute';
    select.style.opacity = '0';
    select.style.pointerEvents = 'none';
    select.style.width = '0';
    select.style.height = '0';
    select.tabIndex = -1;

    // Create Trigger
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'crm-dropdown-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    function renderTriggerContent() {
      let triggerHtml = '';
      if (iconSvg) {
        triggerHtml += `<span class="crm-dropdown-leading-icon">${iconSvg}</span>`;
      }

      if (isMultiple) {
        const selectedOptions = Array.from(select.options).filter(o => o.selected && o.value !== '');
        if (selectedOptions.length === 0) {
          const firstOpt = select.options[0];
          const defaultLabel = (firstOpt && firstOpt.value === '') ? firstOpt.textContent.trim() : allOptionText;
          triggerHtml += `<span class="crm-dropdown-text">${escapeHtml(defaultLabel)}</span>`;
        } else if (selectedOptions.length === 1) {
          triggerHtml += `<span class="crm-dropdown-text">${escapeHtml(selectedOptions[0].textContent.trim())}</span>`;
        } else {
          const firstLabel = selectedOptions[0].textContent.trim();
          triggerHtml += `<span class="crm-dropdown-text">${escapeHtml(firstLabel)}</span>`;
          triggerHtml += `<span class="crm-dropdown-count-badge">+${selectedOptions.length - 1}</span>`;
        }
      } else {
        const selectedOption = select.options[select.selectedIndex] || select.options[0];
        const initialText = selectedOption ? selectedOption.textContent.trim() : '';
        triggerHtml += `<span class="crm-dropdown-text">${escapeHtml(initialText)}</span>`;
      }

      triggerHtml += SVG_CHEVRON;
      trigger.innerHTML = triggerHtml;
    }

    renderTriggerContent();

    // Create Dropdown Menu
    const menu = document.createElement('div');
    menu.className = 'crm-dropdown-menu';
    menu.setAttribute('role', 'listbox');

    let searchInput = null;
    if (searchable) {
      const searchWrap = document.createElement('div');
      searchWrap.className = 'crm-dropdown-search-wrap';
      searchWrap.innerHTML = `<span class="crm-dropdown-search-icon">${SVG_SEARCH}</span>`;

      searchInput = document.createElement('input');
      searchInput.type = 'text';
      searchInput.className = 'crm-dropdown-search-input';
      searchInput.placeholder = searchPlaceholder;
      searchInput.autocomplete = 'off';

      searchWrap.appendChild(searchInput);
      menu.appendChild(searchWrap);
    }

    const list = document.createElement('ul');
    list.className = 'crm-dropdown-list';

    function syncDropdownItems() {
      list.innerHTML = '';
      Array.from(select.options).forEach((opt) => {
        if (opt.disabled || opt.style.display === 'none' || opt.hidden) return;
        if (opt.parentElement && opt.parentElement.tagName === 'OPTGROUP') {
          if (opt.parentElement.disabled || opt.parentElement.style.display === 'none') {
            return;
          }
        }

        const item = document.createElement('li');
        item.className = 'crm-dropdown-item' + (isMultiple ? ' is-multiselect-checkbox' : '');
        item.setAttribute('role', 'option');
        item.dataset.value = opt.value;
        if (opt.dataset.categoryId) {
          item.dataset.categoryId = opt.dataset.categoryId;
        }

        const isSelected = isMultiple ? opt.selected : opt.value === select.value;
        if (isSelected) {
          item.classList.add('is-selected');
          item.setAttribute('aria-selected', 'true');
        }

        if (isMultiple) {
          const checkboxHtml = `<span class="crm-dropdown-checkbox-box">${isSelected ? SVG_CHECK : ''}</span>`;
          item.innerHTML = `${checkboxHtml}<span class="crm-dropdown-item-text">${escapeHtml(opt.textContent.trim())}</span>`;
        } else {
          item.innerHTML = `<span class="crm-dropdown-item-text">${escapeHtml(opt.textContent.trim())}</span>${SVG_CHECK}`;
        }

        item.addEventListener('click', (e) => {
          if (isMultiple) {
            e.preventDefault();
            e.stopPropagation();
            toggleMultiItem(opt, item);
          } else {
            selectItem(opt.value, opt.textContent.trim());
          }
        });

        list.appendChild(item);
      });

      renderTriggerContent();
    }

    function toggleMultiItem(opt, item) {
      if (opt.value === '') {
        // "All" / Clear Option clicked
        Array.from(select.options).forEach(o => o.selected = false);
        opt.selected = true;
      } else {
        // Normal option toggled
        const firstOpt = select.options[0];
        if (firstOpt && firstOpt.value === '') {
          firstOpt.selected = false;
        }
        opt.selected = !opt.selected;

        // If nothing is selected, re-select the "All" option if present
        const anySelected = Array.from(select.options).some(o => o.selected && o.value !== '');
        if (!anySelected && firstOpt && firstOpt.value === '') {
          firstOpt.selected = true;
        }
      }

      // Update item visual state
      list.querySelectorAll('.crm-dropdown-item').forEach(it => {
        const option = Array.from(select.options).find(o => o.value === it.dataset.value);
        const sel = option ? option.selected : false;
        it.classList.toggle('is-selected', sel);
        it.setAttribute('aria-selected', sel ? 'true' : 'false');
        const box = it.querySelector('.crm-dropdown-checkbox-box');
        if (box) box.innerHTML = sel ? SVG_CHECK : '';
      });

      renderTriggerContent();
      select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    syncDropdownItems();
    select.addEventListener('crm-dropdown:update', syncDropdownItems);
    select.addEventListener('change', () => {
      renderTriggerContent();
      if (!isMultiple) {
        list.querySelectorAll('.crm-dropdown-item').forEach((it) => {
          const isMatch = it.dataset.value === select.value;
          it.classList.toggle('is-selected', isMatch);
          it.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
      }
    });
    menu.appendChild(list);

    // Multiselect Footer Actions (Clear / Done)
    if (isMultiple) {
      const footer = document.createElement('div');
      footer.className = 'crm-dropdown-menu-footer';
      footer.innerHTML = `
        <button type="button" class="crm-dropdown-menu-footer-btn js-clear-multi">${isRtl ? 'إلغاء التحديد' : 'Clear All'}</button>
        <button type="button" class="crm-dropdown-menu-footer-btn js-close-multi" style="color:var(--red,#ef4444);font-weight:800;">${isRtl ? 'تم' : 'Done'}</button>
      `;

      footer.querySelector('.js-clear-multi').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        Array.from(select.options).forEach(o => o.selected = false);
        const firstOpt = select.options[0];
        if (firstOpt && firstOpt.value === '') {
          firstOpt.selected = true;
        }
        syncDropdownItems();
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });

      footer.querySelector('.js-close-multi').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeMenu();
        trigger.focus();
      });

      menu.appendChild(footer);
    }

    // Insert wrapper into DOM
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    wrapper.appendChild(trigger);
    wrapper.appendChild(menu);

    // State management
    function positionMenu() {
      menu.style.insetInlineStart = '0';
      menu.style.insetInlineEnd = 'auto';
      menu.style.left = '';
      menu.style.right = '';

      const mRect = menu.getBoundingClientRect();
      const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

      if (isRtl) {
        if (mRect.left < 8) {
          menu.style.insetInlineStart = 'auto';
          menu.style.insetInlineEnd = '0';
        }
      } else {
        if (mRect.right > window.innerWidth - 8) {
          menu.style.insetInlineStart = 'auto';
          menu.style.insetInlineEnd = '0';
        }
      }
    }

    function openMenu() {
      document.querySelectorAll('.crm-dropdown.is-open').forEach((d) => {
        if (d !== wrapper) d.classList.remove('is-open');
      });

      wrapper.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      positionMenu();

      if (searchInput) {
        searchInput.value = '';
        filterItems('');
        setTimeout(() => searchInput.focus(), 30);
      } else {
        trigger.focus();
      }

      const selectedItem = list.querySelector('.is-selected');
      if (selectedItem) {
        selectedItem.scrollIntoView({ block: 'nearest', inline: 'nearest' });
      }
    }

    function closeMenu() {
      wrapper.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      menu.style.insetInlineStart = '';
      menu.style.insetInlineEnd = '';
      menu.style.left = '';
      menu.style.right = '';
    }

    function toggleMenu() {
      if (wrapper.classList.contains('is-open')) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    function selectItem(value, labelText) {
      if (select.value !== value) {
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }

      const textEl = trigger.querySelector('.crm-dropdown-text');
      if (textEl) textEl.textContent = labelText;

      list.querySelectorAll('.crm-dropdown-item').forEach((it) => {
        const isMatch = it.dataset.value === value;
        it.classList.toggle('is-selected', isMatch);
        it.setAttribute('aria-selected', isMatch ? 'true' : 'false');
      });

      closeMenu();
      trigger.focus();
    }

    function filterItems(query) {
      const q = query.trim().toLowerCase();
      let matches = 0;

      list.querySelectorAll('.crm-dropdown-item').forEach((item) => {
        const text = item.textContent.toLowerCase();
        const visible = text.includes(q);
        item.style.display = visible ? '' : 'none';
        if (visible) matches++;
      });

      let noRes = list.querySelector('.crm-dropdown-no-results');
      if (matches === 0) {
        if (!noRes) {
          noRes = document.createElement('li');
          noRes.className = 'crm-dropdown-no-results';
          noRes.textContent = noResultsText;
          list.appendChild(noRes);
        }
      } else if (noRes) {
        noRes.remove();
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', (e) => filterItems(e.target.value));
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeMenu();
          trigger.focus();
        } else if (e.key === 'ArrowDown') {
          e.preventDefault();
          const firstVisible = Array.from(list.querySelectorAll('.crm-dropdown-item')).find(
            (it) => it.style.display !== 'none'
          );
          if (firstVisible) firstVisible.focus();
        }
      });
    }

    // Trigger click
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      toggleMenu();
    });

    // Keyboard support on trigger
    trigger.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openMenu();
      } else if (e.key === 'Escape') {
        closeMenu();
      }
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!wrapper.contains(e.target)) {
        closeMenu();
      }
    });
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function initAllDropdowns(root = document) {
    const selector = 'select:not([data-no-crm-dropdown]):not(.flatpickr-monthDropdown-months):not(.swal2-select):not(.fc-select)';
    root.querySelectorAll(selector).forEach(buildCustomDropdown);
  }

  // Auto-init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initAllDropdowns());
  } else {
    initAllDropdowns();
  }

  // Auto-init on dynamic insertion
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          if (node.matches && node.matches('select:not([data-no-crm-dropdown])')) {
            buildCustomDropdown(node);
          } else if (node.querySelectorAll) {
            initAllDropdowns(node);
          }
        }
      });
    });
  });
  observer.observe(document.documentElement, { childList: true, subtree: true });

  window.initCrmDropdowns = initAllDropdowns;
})();
