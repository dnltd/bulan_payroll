{{-- Desktop Search --}}
<form id="headerSearchForm" class="position-relative d-none d-md-block" autocomplete="off">
  <div class="input-group input-group-sm">
    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
    <input type="text" id="headerSearch" class="form-control border-0 shadow-sm" placeholder="Search..." value="{{ request('search') }}">
    <button type="button" id="resetSearch" class="btn btn-outline-secondary btn-sm d-none">
      <i class="bi bi-x-circle"></i>
    </button>
  </div>
  <ul id="searchSuggestions"
      class="list-group position-absolute w-100 shadow-sm"
      style="z-index:2000; top:100%; display:none; max-height:250px; overflow-y:auto; transition: all 0.2s ease;">
  </ul>
</form>

{{-- Mobile Search Toggle --}}
<button class="btn d-md-none" id="toggleMobileSearch">
  <i class="bi bi-search fs-5"></i>
</button>

{{-- Inline Mobile Search Bar --}}
<div id="mobileSearchWrapper" class="d-md-none px-2 py-2 bg-white border-top" style="display:none;">
  <form id="mobileSearchForm" class="position-relative" autocomplete="off">
    <div class="input-group input-group-sm">
      <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
      <input type="text" id="mobileSearch" class="form-control border-0 shadow-sm" placeholder="Search..." value="{{ request('search') }}">
      <button type="button" id="resetMobileSearch" class="btn btn-outline-secondary btn-sm d-none">
        <i class="bi bi-x-circle"></i>
      </button>
    </div>
    <ul id="mobileSuggestions" class="list-group position-absolute w-100 shadow-sm"
        style="z-index:2000; top:100%; display:none; max-height:250px; overflow-y:auto; transition: all 0.2s ease;">
    </ul>
  </form>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
  const currentPage = @json(
    request()->routeIs('admin.payroll.*') ? 'payroll' :
    (request()->routeIs('admin.attendance.*') ? 'attendance' :
    (request()->routeIs('admin.deductions.*') ? 'deductions' :
    (request()->routeIs('admin.round_trip.*') ? 'round_trip' : 'employees'))))
  ;

  // ===== Elements =====
  const searchInput = document.getElementById('headerSearch');
  const suggestionsBox = document.getElementById('searchSuggestions');
  const resetSearchBtn = document.getElementById('resetSearch');

  const toggleMobileBtn = document.getElementById('toggleMobileSearch');
  const mobileWrapper = document.getElementById('mobileSearchWrapper');
  const mobileInput = document.getElementById('mobileSearch');
  const mobileSuggestions = document.getElementById('mobileSuggestions');
  const resetMobileBtn = document.getElementById('resetMobileSearch');

  let searchTimeout = null;

  // ===== Helpers =====
  function updateResetBtn(inputEl, btnEl) {
    btnEl.classList.toggle('d-none', inputEl.value.trim() === "");
  }

  function fetchSuggestions(query, listElement, callback) {
    fetch(`{{ route('admin.autocomplete') }}?term=${encodeURIComponent(query)}&context=${currentPage}`)
      .then(res => res.json())
      .then(data => {
        listElement.innerHTML = '';
        if (!data.length) { listElement.style.display = 'none'; return; }
        data.forEach(item => {
          const li = document.createElement('li');
          li.classList.add('list-group-item', 'list-group-item-action');
          li.textContent = item.name;
          li.addEventListener('click', () => {
            // Fill input immediately
            searchInput.value = item.name;
            updateResetBtn(searchInput, resetSearchBtn);
            if (mobileInput) {
              mobileInput.value = item.name;
              updateResetBtn(mobileInput, resetMobileBtn);
            }
            // Redirect including 'name' so it can be restored after reload
            const url = buildUrl({ employee_id: item.id, name: item.name });
            window.location.href = url;
          });
          listElement.appendChild(li);
        });
        listElement.style.display = 'block';
      });
  }

  function buildBaseUrl() {
    if (currentPage === 'employees') return "{{ route('admin.employees.index') }}";
    if (currentPage === 'payroll') return "{{ route('admin.payroll.index') }}";
    if (currentPage === 'attendance') return "{{ route('admin.attendance.index') }}";
    if (currentPage === 'deductions') return "{{ route('admin.deductions.index') }}";
    if (currentPage === 'round_trip') return "{{ route('admin.round_trip.index') }}";
    return "/";
  }

  function buildUrl(params = {}) {
    let url = buildBaseUrl() + "?";

    if (params.search) url += "search=" + encodeURIComponent(params.search) + "&";
    if (params.employee_id) url += "employee_id=" + encodeURIComponent(params.employee_id) + "&";
    if (params.name) url += "name=" + encodeURIComponent(params.name) + "&";

    if (currentPage === 'payroll') {
      const startDate = "{{ request('start_date') }}";
      const endDate   = "{{ request('end_date') }}";
      if (startDate && endDate) url += "start_date=" + encodeURIComponent(startDate) + "&end_date=" + encodeURIComponent(endDate) + "&";
    } else if (currentPage === 'attendance' || currentPage === 'round_trip') {
      const date = "{{ request('date') }}";
      if (date) url += "date=" + encodeURIComponent(date) + "&";
    } else if (currentPage === 'deductions') {
      const type = "{{ request('type') }}";
      if (type) url += "type=" + encodeURIComponent(type) + "&";
    }

    return url.replace(/[&?]$/, '');
  }

  function redirectByName(query) {
    window.location.href = buildUrl({ search: query });
  }

  // ===== Desktop Events =====
  searchInput?.addEventListener('input', function() {
    const query = this.value.trim();
    updateResetBtn(searchInput, resetSearchBtn);
    clearTimeout(searchTimeout);
    if (!query) { suggestionsBox.style.display = 'none'; return; }
    searchTimeout = setTimeout(() => fetchSuggestions(query, suggestionsBox, redirectByName), 300);
  });

  resetSearchBtn?.addEventListener('click', () => {
    searchInput.value = '';
    updateResetBtn(searchInput, resetSearchBtn);
    window.location.href = buildBaseUrl();
  });

  document.getElementById('headerSearchForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const query = searchInput.value.trim();
    if (query) redirectByName(query);
  });

  // ===== Mobile Events =====
  toggleMobileBtn?.addEventListener('click', () => {
    mobileWrapper.style.display = "block";
    mobileInput.focus();
    toggleMobileBtn.style.display = "none";
  });

  mobileInput?.addEventListener('input', function() {
    const query = this.value.trim();
    updateResetBtn(mobileInput, resetMobileBtn);
    if (!query) { mobileSuggestions.style.display = 'none'; return; }
    fetchSuggestions(query, mobileSuggestions, (id, name) => {
      mobileInput.value = name;
      updateResetBtn(mobileInput, resetMobileBtn);
      const url = buildUrl({ employee_id: id, name: name });
      window.location.href = url;
      mobileWrapper.style.display = "none";
      mobileSuggestions.style.display = "none";
    });
  });

  resetMobileBtn?.addEventListener('click', () => {
    mobileInput.value = '';
    updateResetBtn(mobileInput, resetMobileBtn);
    window.location.href = buildBaseUrl();
  });

  document.getElementById('mobileSearchForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const query = mobileInput.value.trim();
    if (query) redirectByName(query);
  });

  // ===== On Page Load: Restore search inputs and show reset button =====
  const urlParams = new URLSearchParams(window.location.search);
  const nameParam = urlParams.get('name') || urlParams.get('search');
  if (nameParam) {
    searchInput.value = nameParam;
    updateResetBtn(searchInput, resetSearchBtn);
    if (mobileInput) {
      mobileInput.value = nameParam;
      updateResetBtn(mobileInput, resetMobileBtn);
    }
  }
});
</script>
@endpush
