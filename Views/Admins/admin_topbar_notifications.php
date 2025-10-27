<?php
// Views/Admins/admin_topbar_notifications.php
?>
<div class="d-flex align-items-center gap-4">
    <div class="dropdown">
        <button class="btn btn-light position-relative" type="button" id="btnNoti" data-bs-toggle="dropdown" aria-expanded="false">
            🔔
            <span id="notiBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnNoti" style="min-width:320px;">
            <li><h6 class="dropdown-header">Thông báo</h6></li>
            <div id="notiList">
                <li class="px-3 py-2 text-muted">Đang tải...</li>
            </div>
            <li><hr class="dropdown-divider"></li>
            <li><button id="markReadBtn" class="dropdown-item">Đánh dấu đã đọc</button></li>
        </ul>
    </div>
    <span class="admin-avatar">A</span>
    <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">Admin</button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Profile</a></li>
        </ul>
    </div>
</div>
<script>
async function fetchNotifications(){
  try{
    const res = await fetch('/GoodZStore/Views/Admins/notifications_api.php?limit=10', {cache:'no-store'});
    const data = await res.json();
    const badge = document.getElementById('notiBadge');
    if (data.unread && data.unread > 0) {
      badge.style.display = 'inline-block';
      badge.textContent = data.unread;
    } else {
      badge.style.display = 'none';
    }
    const list = document.getElementById('notiList');
    list.innerHTML = '';
    if (data.items && data.items.length){
      data.items.forEach(it => {
        const li = document.createElement('li');
        li.className = 'px-3 py-2';
        const a = document.createElement(it.link ? 'a' : 'div');
        if (it.link) { a.href = it.link; a.style.textDecoration='none'; }
        a.innerHTML = `<div><strong>${it.type}</strong> - ${it.message}</div><small class="text-muted">${it.created_at}</small>`;
        li.appendChild(a);
        list.appendChild(li);
      });
    } else {
      const li = document.createElement('li'); li.className = 'px-3 py-2 text-muted'; li.textContent = 'Không có thông báo.'; list.appendChild(li);
    }
  }catch(e){ console.error(e); }
}
setInterval(fetchNotifications, 10000);
window.addEventListener('load', fetchNotifications);

const markBtn = document.getElementById('markReadBtn');
if (markBtn) {
  markBtn.addEventListener('click', async ()=>{
    await fetch('/GoodZStore/Views/Admins/notifications_api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=mark_all_read'});
    fetchNotifications();
  });
}
</script>
