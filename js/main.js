/* AL-SYUKROSMART OPS — main.js */
'use strict';

/* TOAST */
function toast(msg, type='info', duration=4000){
    const c=document.getElementById('toastContainer');
    if(!c){console.log(`[${type}] ${msg}`);return;}
    const el=document.createElement('div');
    el.className=`toast toast-${type}`;
    const ic={success:'✅',error:'❌',warning:'⚠️',info:'ℹ️'};
    el.innerHTML=`<span style="font-size:18px;">${ic[type]||'ℹ️'}</span><span style="flex:1;line-height:1.4;">${msg}</span>`;
    el.onclick=()=>el.remove();
    c.appendChild(el);
    setTimeout(()=>{el.style.transition='opacity .4s,transform .4s';el.style.opacity='0';el.style.transform='translateX(120%)';setTimeout(()=>el.remove(),400);},duration);
}

/* API FETCH */
async function apiFetch(resource, action, data={}, method='POST'){
    try {
        let url, opts={};
        if(method==='GET'){
            url=`api/crud.php?${new URLSearchParams({...data,resource,action})}`;
            opts={method:'GET'};
        } else {
            url='api/crud.php';
            const b=new FormData();
            b.append('resource',resource); b.append('action',action);
            Object.entries(data).forEach(([k,v])=>b.append(k,v));
            opts={method:'POST',body:b};
        }
        const res=await fetch(url,opts);
        return await res.json();
    } catch(err){ return {ok:false,msg:'Koneksi gagal: '+err.message}; }
}

/* MODAL */
function openModal(id){document.getElementById(id)?.classList.add('open');}
function closeModal(id){document.getElementById(id)?.classList.remove('open');}

/* TABLE SEARCH */
function filterTable(q,tableId=null){
    const rows=document.querySelectorAll(tableId?`#${tableId} tbody tr`:'tbody tr');
    const ql=q.toLowerCase();
    rows.forEach(r=>r.style.display=r.textContent.toLowerCase().includes(ql)?'':'none');
}

/* PRINT */
function printPage(type){window.open(`print.php?type=${type}`,'_blank');}

document.addEventListener('DOMContentLoaded',()=>{

/* MOBILE SIDEBAR */
const menuBtn=document.getElementById('menuToggle');
const sb=document.querySelector('.sidebar');
const sOv=document.getElementById('sidebarOverlay');
if(menuBtn){menuBtn.addEventListener('click',()=>{sb?.classList.toggle('open');if(sOv)sOv.style.cssText=sb?.classList.contains('open')?'display:block;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;':'display:none;';});}
if(sOv){sOv.addEventListener('click',()=>{sb?.classList.remove('open');sOv.style.display='none';});}

/* MODAL */
document.querySelectorAll('.modal-overlay').forEach(ov=>ov.addEventListener('click',e=>{if(e.target===ov)ov.classList.remove('open');}));
document.querySelectorAll('.modal-close').forEach(btn=>btn.addEventListener('click',()=>btn.closest('.modal-overlay')?.classList.remove('open')));

/* TABLE SEARCH INPUT */
document.querySelectorAll('#tableSearch').forEach(inp=>inp.addEventListener('input',()=>filterTable(inp.value)));

/* SORTABLE HEADERS */
document.querySelectorAll('th[data-sort]').forEach(th=>{
    th.style.cursor='pointer'; th.title='Klik untuk mengurutkan';
    let asc=true;
    th.addEventListener('click',()=>{
        const tbody=th.closest('table')?.querySelector('tbody');
        if(!tbody)return;
        const idx=[...th.parentElement.children].indexOf(th);
        const rows=[...tbody.querySelectorAll('tr')];
        rows.sort((a,b)=>{
            const A=a.cells[idx]?.textContent.trim()||'',B=b.cells[idx]?.textContent.trim()||'';
            const nA=parseFloat(A),nB=parseFloat(B);
            if(!isNaN(nA)&&!isNaN(nB))return asc?nA-nB:nB-nA;
            return asc?A.localeCompare(B,'id'):B.localeCompare(A,'id');
        });
        rows.forEach(r=>tbody.appendChild(r));
        document.querySelectorAll('th[data-sort]').forEach(t=>t.textContent=t.textContent.replace(/ [▲▼]$/,''));
        th.textContent+=(asc?' ▲':' ▼'); asc=!asc;
    });
});

/* PASSWORD TOGGLE */
document.querySelectorAll('.eye-toggle').forEach(btn=>btn.addEventListener('click',()=>{
    const inp=btn.previousElementSibling; if(!inp)return;
    inp.type=inp.type==='password'?'text':'password';
    btn.textContent=inp.type==='password'?'👁️':'🙈';
}));

/* DEMO ACCOUNT BUTTONS */
document.querySelectorAll('.demo-btn').forEach(btn=>btn.addEventListener('click',()=>{
    const u=document.getElementById('username'),p=document.getElementById('password');
    if(u)u.value=btn.dataset.user||''; if(p)p.value=btn.dataset.pass||'';
}));

/* NOTIFICATION DROPDOWN */
const nb=document.getElementById('notifBtn'),nd=document.getElementById('notifDropdown');
if(nb&&nd){nb.addEventListener('click',e=>{e.stopPropagation();nd.classList.toggle('show');});document.addEventListener('click',()=>nd.classList.remove('show'));}

/* DATE DISPLAY */
const de=document.getElementById('currentDate');
if(de){const n=new Date();const days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];const mo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];de.textContent=`${days[n.getDay()]}, ${n.getDate()} ${mo[n.getMonth()]} ${n.getFullYear()}`;}

/* ANIMATE KPI NUMBERS */
document.querySelectorAll('.kpi-val').forEach(el=>{
    const raw=parseInt(el.textContent.replace(/[^0-9]/g,''));
    if(!raw||raw<2)return;
    let cur=0; const step=Math.ceil(raw/32);
    const t=setInterval(()=>{cur=Math.min(cur+step,raw);el.textContent=cur.toLocaleString('id-ID');if(cur>=raw)clearInterval(t);},25);
});

/* PROGRESS BAR ANIMATION */
document.querySelectorAll('.progress-fill').forEach(bar=>{
    const w=bar.style.width; bar.style.width='0';
    setTimeout(()=>{bar.style.transition='width .8s ease';bar.style.width=w;},150);
});

/* AUTO CLOSE FLASH ALERTS */
setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>{a.style.transition='opacity .5s,max-height .5s,padding .5s,margin .5s';a.style.opacity='0';a.style.maxHeight='0';a.style.padding='0';a.style.margin='0';});},5000);

/* UPLOAD DROP ZONE */
document.querySelectorAll('.upload-drop-zone').forEach(zone=>{
    zone.addEventListener('dragover',e=>{e.preventDefault();zone.classList.add('drag-over');});
    zone.addEventListener('dragleave',()=>zone.classList.remove('drag-over'));
    zone.addEventListener('drop',e=>{e.preventDefault();zone.classList.remove('drag-over');const f=e.dataTransfer.files;if(f.length)toast(`📁 ${f[0].name} siap diupload`,'info');});
    zone.addEventListener('click',()=>zone.querySelector('input[type=file]')?.click());
});

/* CONFIRM DATA ATTR */
document.querySelectorAll('[data-confirm]').forEach(btn=>btn.addEventListener('click',e=>{if(!confirm(btn.dataset.confirm||'Yakin?'))e.preventDefault();}));

});
