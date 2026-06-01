<script>
  (function () {
    const availableAttributes = Array.isArray(window.availableProductAttributes) ? window.availableProductAttributes : [];
    const attributesWrap = document.getElementById('attributesWrap');
    const variationsWrap = document.getElementById('variationsWrap');
    const emptyVariations = document.getElementById('emptyVariations');
    const attributesJson = document.getElementById('attributesJson');
    const variationsJson = document.getElementById('variationsJson');
    const navCount = document.getElementById('variationNavCount');
    const attributeSelect = document.getElementById('globalAttributeSelect');
    const addAttributeBtn = document.getElementById('addGlobalAttributeBtn');
    const generateBtn = document.getElementById('generateVariationsBtn');

    if (!attributesWrap || !variationsWrap) return;

    let attributes = Array.isArray(window.initialProductAttributes) ? window.initialProductAttributes : [];
    let variations = Array.isArray(window.initialProductVariations) ? window.initialProductVariations : [];

    document.querySelectorAll('[data-builder-tab]').forEach(tab => {
      tab.addEventListener('click', () => activateTab(tab.dataset.builderTab));
    });

    function activateTab(name) {
      document.querySelectorAll('[data-builder-tab]').forEach(tab => tab.classList.toggle('active', tab.dataset.builderTab === name));
      document.getElementById('attributesPanel').classList.toggle('d-none', name !== 'attributes');
      document.getElementById('attributesPanel').classList.toggle('active', name === 'attributes');
      document.getElementById('variationsPanel').classList.toggle('d-none', name !== 'variations');
      document.getElementById('variationsPanel').classList.toggle('active', name === 'variations');
    }

    function normalizeAttribute(attribute) {
      const values = Array.isArray(attribute.values) ? attribute.values : [];
      return {
        id: attribute.id || null,
        name: attribute.name || '',
        values,
        selected: Array.isArray(attribute.selected) ? attribute.selected : values,
        used: attribute.used !== false
      };
    }

    function addGlobalAttribute() {
      const id = Number(attributeSelect.value);
      if (!id || attributes.some(attribute => Number(attribute.id) === id)) return;

      const source = availableAttributes.find(attribute => Number(attribute.id) === id);
      if (!source) return;

      attributes.push(normalizeAttribute({
        id: source.id,
        name: source.name,
        values: source.values || [],
        selected: source.values || [],
        used: true
      }));
      attributeSelect.value = '';
      renderAttributes();
    }

    function renderAttributes() {
      attributesWrap.innerHTML = '';
      attributes = attributes.map(normalizeAttribute).filter(attribute => attribute.name);

      attributes.forEach((attribute, index) => {
        const values = attribute.values.map(value => `
          <label class="badge bg-success-subtle text-success me-2 mb-2 p-2">
            <input type="checkbox" class="form-check-input me-1 attribute-value-check" data-index="${index}" value="${escapeHtml(value)}" ${attribute.selected.includes(value) ? 'checked' : ''}>
            ${escapeHtml(value)}
          </label>
        `).join('');

        const card = document.createElement('div');
        card.className = 'card border mb-3';
        card.innerHTML = `
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong>${escapeHtml(attribute.name)}</strong>
            <button type="button" class="btn btn-outline-danger btn-sm remove-attribute" data-index="${index}">Remove</button>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Value(s)</label>
              <div class="border rounded p-2">${values || '<span class="text-muted small">No values available</span>'}</div>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input attribute-used" data-index="${index}" ${attribute.used ? 'checked' : ''}>
              <label class="form-check-label">Used for variations</label>
            </div>
          </div>
        `;
        attributesWrap.appendChild(card);
      });

      bindAttributeEvents();
      syncAttributes();
    }

    function bindAttributeEvents() {
      attributesWrap.querySelectorAll('.attribute-value-check').forEach(input => {
        input.addEventListener('change', () => {
          const attribute = attributes[input.dataset.index];
          attribute.selected = Array.from(attributesWrap.querySelectorAll(`.attribute-value-check[data-index="${input.dataset.index}"]:checked`)).map(item => item.value);
          syncAttributes();
        });
      });

      attributesWrap.querySelectorAll('.attribute-used').forEach(input => {
        input.addEventListener('change', () => {
          attributes[input.dataset.index].used = input.checked;
          syncAttributes();
        });
      });

      attributesWrap.querySelectorAll('.remove-attribute').forEach(button => {
        button.addEventListener('click', () => {
          attributes.splice(button.dataset.index, 1);
          renderAttributes();
        });
      });
    }

    function generateCombinations(attributeList) {
      return attributeList.reduce((acc, attribute) => {
        const next = [];
        acc.forEach(existing => {
          attribute.selected.forEach(value => next.push({ ...existing, [attribute.name]: value }));
        });
        return next;
      }, [{}]);
    }

    function generateVariations() {
      syncAttributes();
      const usable = attributes.filter(attribute => attribute.used && attribute.selected.length);
      const existingByName = new Map(variations.map(variation => [variation.name, variation]));

      variations = generateCombinations(usable).map((map, index) => {
        const name = Object.entries(map).map(([key, value]) => `${key}: ${value}`).join(' / ');
        return existingByName.get(name) || {
          name,
          attributes: map,
          sku: '',
          price: '',
          sale_price: '',
          stock_quantity: '',
          enabled: true,
          default: index === 0
        };
      });

      renderVariations();
      activateTab('variations');
    }

    function renderVariations() {
      variationsWrap.innerHTML = '';
      navCount.textContent = variations.length;
      emptyVariations.style.display = variations.length ? 'none' : 'block';

      variations.forEach((variation, index) => {
        const card = document.createElement('div');
        card.className = 'card border mb-3 variation-card';
        card.dataset.index = index;
        card.innerHTML = `
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong>#${index + 1} ${escapeHtml(variation.name || '')}</strong>
            <div class="d-flex gap-2">
              <span class="badge ${variation.enabled === false ? 'bg-secondary' : 'bg-success'} align-self-center">${variation.enabled === false ? 'Disabled' : 'Enabled'}</span>
              <button type="button" class="btn btn-outline-danger btn-sm remove-variation">Remove</button>
              <button type="button" class="btn btn-outline-secondary btn-sm toggle-variation" data-bs-toggle="collapse" data-bs-target="#variationBody${index}">Open</button>
            </div>
          </div>
          <div class="collapse ${index === 0 ? 'show' : ''}" id="variationBody${index}">
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">SKU</label>
                  <input type="text" class="form-control variation-sku" value="${escapeHtml(variation.sku || '')}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">MRP / Compare (INR)</label>
                  <input type="number" step="0.01" class="form-control variation-price" value="${escapeHtml(variation.price || '')}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Selling Price (INR)</label>
                  <input type="number" step="0.01" class="form-control variation-sale-price" value="${escapeHtml(variation.sale_price || '')}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Stock Quantity</label>
                  <input type="number" class="form-control variation-stock" value="${escapeHtml(variation.stock_quantity || '')}">
                </div>
                <div class="col-md-8">
                  <label class="form-label">Variation Settings</label>
                  <div class="border rounded p-3 d-flex gap-4">
                    <label class="form-check-label"><input type="radio" name="default_variation" class="form-check-input variation-default" ${variation.default ? 'checked' : ''}> Default variation</label>
                    <label class="form-check-label"><input type="checkbox" class="form-check-input variation-enabled" ${variation.enabled === false ? '' : 'checked'}> Enabled</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        variationsWrap.appendChild(card);
      });

      bindVariationEvents();
      syncVariations();
    }

    function bindVariationEvents() {
      variationsWrap.querySelectorAll('.variation-card').forEach(card => {
        const index = Number(card.dataset.index);
        card.querySelector('.remove-variation').addEventListener('click', () => {
          variations.splice(index, 1);
          renderVariations();
        });
        card.querySelector('.variation-sku').addEventListener('input', e => { variations[index].sku = e.target.value; syncVariations(); });
        card.querySelector('.variation-price').addEventListener('input', e => { variations[index].price = e.target.value; syncVariations(); });
        card.querySelector('.variation-sale-price').addEventListener('input', e => { variations[index].sale_price = e.target.value; syncVariations(); });
        card.querySelector('.variation-stock').addEventListener('input', e => { variations[index].stock_quantity = e.target.value; syncVariations(); });
        card.querySelector('.variation-enabled').addEventListener('change', e => { variations[index].enabled = e.target.checked; renderVariations(); });
        card.querySelector('.variation-default').addEventListener('change', () => {
          variations = variations.map((variation, i) => ({ ...variation, default: i === index }));
          renderVariations();
        });
      });
    }

    function syncAttributes() {
      attributesJson.value = JSON.stringify(attributes);
    }

    function syncVariations() {
      variationsJson.value = JSON.stringify(variations);
      navCount.textContent = variations.length;
    }

    function escapeHtml(value) {
      return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    addAttributeBtn.addEventListener('click', addGlobalAttribute);
    generateBtn.addEventListener('click', generateVariations);

    renderAttributes();
    renderVariations();
  })();
</script>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/product/partials/product_variations_script.blade.php ENDPATH**/ ?>