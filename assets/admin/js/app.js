document.addEventListener("DOMContentLoaded",()=>{

    lucide.createIcons();

    if(typeof Chart!=="undefined"){

        const s=document.createElement("script");

        s.src=new URL("dashboard.js", document.currentScript?.src || window.location.href).href;

        document.body.appendChild(s);

    }

});
