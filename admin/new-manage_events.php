<?php 
require '../api/auth.php';
checkUserRole('admin');

require '../config/db_conn.php';

// Fetch organizations for dropdown
$organizations = [];
$orgResult = $conn->query("SELECT id, name FROM neworganizations ORDER BY name");
while ($org = $orgResult->fetch_assoc()) {
    $organizations[] = $org;
}

// (Optional) Fetch admin name
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
if ($admin_id) {
    $stmt = $conn->prepare("SELECT CONCAT(lastName, ', ', firstName, ' ', middleName) AS name FROM newusers WHERE ID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $admin_name = ucwords(strtolower($row['name']));
    }
    $stmt->close();
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "new-manage_events.css";
include '../includes/header.php';
?>

<script>
  const allOrganizations = <?= json_encode($organizations) ?>;
</script>

<button class="hamburger-menu"><span></span><span></span><span></span></button>
<div class="sidebar-overlay"></div>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
  <?php include '../includes/navbar.php'; ?>

  <div class="content">
    <h2 class="page-title">MANAGE EVENTS</h2>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php elseif (!empty($_SESSION['success'])): ?>
      <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="add-event-button">
      <a href="add_event.php" class="btn-add-event">+ Add New Event</a>
    </div>

    <div class="event-table">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Organization</th>
            <th>Event Date</th>
            <th>Status</th>
            <th>Disabled</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="eventsTableBody"></tbody>
      </table>
    </div>

    <div id="paginationControls" class="pagination-controls"></div>
  </div>
</main>

<style>.pagination-controls {
    margin-top: .7em;
}
.pagination-controls button {
    padding: 5px 10px;
    margin: 0 3px;
    border: none;
    background: #ddd;
    cursor: pointer;
    border-radius: 3px;
}
.pagination-controls button.active {
    background: #333;
    color: #fff;
}
.action-btn {
    padding: 10px 16px;
    background-color: #2A4365;
    color: #fff;
    text-decoration: none;
    border-radius: 999px;
    font-weight: bold;
    transition: background-color 0.3s;
}
.action-btn:hover {
    background-color: #1f2f47;
}
  .disabled-event { opacity: 0.5; background: #f9f9f9; }
  .status-select, .edit-title, .edit-date, .org-select { padding: 4px; }
  .btn-edit, .btn-toggle-disable, .btn-cancel { margin-right: 5px; padding: 4px 8px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const body = document.getElementById('eventsTableBody');
  const pager = document.getElementById('paginationControls');
  const minRows = 5;

  fetchPage();

  function fetchPage(page = 1) {
    body.innerHTML = '<tr><td colspan="7">Loading events...</td></tr>';
    fetch(`get_events.php?page=${page}`)
      .then(r => r.json())
      .then(d => { renderRows(d.events); renderPager(d.total, d.perPage, page); })
      .catch(() => { alert('Error loading events.'); });
  }

  function renderRows(events) {
    body.innerHTML = '';
    events.forEach(e => {
      const tr = document.createElement('tr');
      tr.dataset.id = e.id;
      if (e.is_disabled) tr.classList.add('disabled-event');
      tr.innerHTML = `
        <td>${escape(e.title)}</td>
        <td>${escape(e.description)}</td>
        <td>${escape(e.org_name)}</td>
        <td>${formatDisplay(e.event_date)}</td>
        <td class="status-cell">${capitalize(e.status)}</td>
        <td class="disabled-cell">${e.is_disabled ? 'Yes' : 'No'}</td>
        <td>
          <button class="btn-edit">Edit</button>
          <button class="btn-toggle-disable">${e.is_disabled ? 'Enable' : 'Disable'}</button>
        </td>`;
      body.appendChild(tr);
    });
    for (let i = events.length; i < minRows; i++) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="7" style="height:40px"></td>`;
      body.appendChild(tr);
    }
    attachListeners();
  }

  function attachListeners() {
    document.querySelectorAll('.btn-edit').forEach(b => b.onclick = onEdit);
    document.querySelectorAll('.btn-toggle-disable').forEach(b => b.onclick = onToggle);
  }

  function onEdit(e) {
  const btn = e.target;
  const tr = btn.closest('tr');

  // Prevent multiple edit modes
  if (document.querySelector('.edit-title')) return;

  const [titleCell, descCell, orgCell, dateCell] = [tr.children[0], tr.children[1], tr.children[2], tr.children[3]];
  const statusCell = tr.querySelector('.status-cell');
  const actions = btn.parentElement;

  const orig = {
    title: titleCell.textContent,
    description: descCell.textContent,
    org: orgCell.textContent,
    date: dateCell.textContent,
    status: statusCell.textContent.toLowerCase()
  };

  const inpTitle = document.createElement('input');
  inpTitle.value = orig.title;
  inpTitle.className = 'edit-title';
  titleCell.innerHTML = ''; titleCell.appendChild(inpTitle);

  const inpDesc = document.createElement('textarea');
  inpDesc.value = orig.description;
  inpDesc.className = 'edit-desc';
  descCell.innerHTML = ''; descCell.appendChild(inpDesc);

  const selectOrg = document.createElement('select');
  selectOrg.className = 'org-select';
  allOrganizations.forEach(o => {
    const opt = document.createElement('option');
    opt.value = o.id; opt.textContent = o.name;
    if (o.name === orig.org) opt.selected = true;
    selectOrg.appendChild(opt);
  });
  orgCell.innerHTML = ''; orgCell.appendChild(selectOrg);

  const inpDate = document.createElement('input');
  inpDate.type = 'date';
  inpDate.value = isoDate(orig.date);
  inpDate.className = 'edit-date';
  dateCell.innerHTML = ''; dateCell.appendChild(inpDate);

  const selectStatus = document.createElement('select');
  selectStatus.className = 'status-select';
  ['under review', 'accepted', 'rejected'].forEach(v => {
    const opt = document.createElement('option');
    opt.value = v; opt.textContent = capitalize(v);
    if (v === orig.status) opt.selected = true;
    selectStatus.appendChild(opt);
  });
  statusCell.innerHTML = ''; statusCell.appendChild(selectStatus);

  btn.textContent = 'Save';

  // Detach current click listener temporarily
  const originalHandler = onEdit;
  btn.onclick = () => saveChanges(tr, inpTitle.value, inpDesc.value, selectOrg.value, inpDate.value, selectStatus.value, btn, cancelBtn);

  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = 'Cancel';
  cancelBtn.className = 'btn-cancel';
  actions.appendChild(cancelBtn);

  cancelBtn.onclick = () => {
    titleCell.textContent = orig.title;
    descCell.textContent = orig.description;
    orgCell.textContent = orig.org;
    dateCell.textContent = orig.date;
    statusCell.textContent = capitalize(orig.status);
    btn.textContent = 'Edit';
    cancelBtn.remove();
    btn.onclick = originalHandler; // Restore original Edit handler
  };
}


  function saveChanges(tr, t, dsc, orgId, d, s, saveBtn, cancelBtn) {
    fetch('update_event_status.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        id: tr.dataset.id,
        title: t,	description: dsc,
        org_id: orgId, event_date: d, status: s
      })
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        tr.children[0].textContent = t;
        tr.children[1].textContent = dsc;
        tr.children[2].textContent = allOrganizations.find(o => o.id == orgId).name;
        tr.children[3].textContent = formatDisplay(d);
        tr.querySelector('.status-cell').textContent = capitalize(s);
        saveBtn.textContent = 'Edit';
        cancelBtn.remove();
        attachListeners();
      } else {
        alert(res.message || 'Save failed');
      }
    })
    .catch(() => alert('Error saving'));
  }

  function onToggle(e) {
    const btn = e.target;
    const tr = btn.closest('tr');
    const id = tr.dataset.id;
    const newState = !tr.classList.contains('disabled-event');
    fetch('toggle_event_disable.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id, disable: newState })
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        tr.classList.toggle('disabled-event', newState);
        tr.querySelector('.disabled-cell').textContent = newState ? 'Yes' : 'No';
        btn.textContent = newState ? 'Enable' : 'Disable';
      } else alert(res.message || 'Toggle failed');
    })
    .catch(() => alert('Error toggling'));
  }

  function renderPager(total, per, current) {
    pager.innerHTML = '';
    const pages = Math.ceil(total / per);
    for (let p = 1; p <= pages; p++) {
      const b = document.createElement('button');
      b.textContent = p;
      if (p === current) b.className = 'active';
      b.onclick = () => fetchPage(p);
      pager.appendChild(b);
    }
  }

  function escape(txt) {
    return txt?.replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]) || '';
  }
  function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
  }
  function formatDisplay(date) {
    return new Date(date).toLocaleDateString(undefined, {month:'short',day:'numeric',year:'numeric'});
  }
  function isoDate(display) {
    const d = new Date(display);
    return isNaN(d) ? '' : `${d.getFullYear()}-${('0'+(d.getMonth()+1)).slice(-2)}-${('0'+d.getDate()).slice(-2)}`;
  }
});
</script>


<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?>
