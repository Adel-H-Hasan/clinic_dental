/* TAQWEEM v4: frame-calibrated monitor opening → volumetric scan → live molar.
   No independent animation clock. All values are pure functions of journey scroll p.
   Image-space calibration: visible monitor quad in treatment still at 9.5 sec,
   source 1280×720: TL(859,194), TR(1147,160), BR(1148,344), BL(861,349).
   Video is a concept, and the scan representation is illustrative. */
(()=>{'use strict';
 const theatre=document.getElementById('theatre'),gate=document.getElementById('portal-gateway');
 const surface=document.getElementById('portal-surface'),xray=document.getElementById('portal-xray');
 const depth=document.getElementById('portal-depth'),grid=document.getElementById('portal-perspective-grid');
 const core=document.getElementById('portal-core'),ray=document.getElementById('portal-depth-ray');
 const locator=document.querySelector('#lens .lens-inner');
 const slices=[...document.querySelectorAll('.portal-slice')];
 if(!theatre||!gate)return;
 const smooth=(a,b,p)=>{const t=Math.max(0,Math.min(1,(p-a)/(b-a)));return t*t*(3-2*t)};
 const phase=(a,b,c,d,p)=>smooth(a,b,p)*(1-smooth(c,d,p));
 const mix=(a,b,t)=>a+(b-a)*t;
 const sourceQuad=[[859,194],[1147,160],[1148,344],[861,349]];
 let cachedSize='';let metrics=null;
 function layout(){
   const W=theatre.clientWidth,H=theatre.clientHeight;
   const cover=Math.max(W/1280,H/720),fitW=1280*cover,fitH=720*cover;
   metrics={W,H,cover,fitW,fitH};cachedSize=`${W}:${H}`;
 }
 function camera(m){
   const W=theatre.clientWidth,H=theatre.clientHeight;
   if(`${W}:${H}`!==cachedSize)layout();
   const {cover,fitW,fitH}=metrics;
   // Only on portrait screens, dolly/pan from the actual patient to the far-right
   // screen. The originally approved 54% crop never contained this monitor.
   const posX=W<=600?mix(.54,.85,m):.5;
   const offX=(W-fitW)*posX,offY=(H-fitH)*.5;
   const raw=sourceQuad.map(([x,y])=>[x*cover+offX,y*cover+offY]);
   const cx=raw.reduce((a,pt)=>a+pt[0],0)/4;
   const cy=raw.reduce((a,pt)=>a+pt[1],0)/4;
   const ox=W*.792,oy=H*.355,scale=1+2.4*m;
   const cxFinal=(sourceQuad.reduce((a,pt)=>a+pt[0],0)/4)*cover+(W-fitW)*(W<=600?.85:.5);
   const finalTx=W*.5-(ox+3.4*(cxFinal-ox));
   const finalTy=H*.5-(oy+3.4*(cy-oy));
   return {posX,tx:m*finalTx,ty:m*finalTy,scale,raw,ox,oy,W,H};
 }
 function update(p,m,cameraState){
   const shot=cameraState||camera(m);
   const {W,H,raw,ox,oy,tx,ty,scale}=shot;
   if(p<.11||p>.67){gate.style.opacity='0';return;}
   const transformed=raw.map(([x,y])=>[ox+tx+scale*(x-ox),oy+ty+scale*(y-oy)]);
   const trackXs=transformed.map(pt=>pt[0]),trackYs=transformed.map(pt=>pt[1]);
   if(locator){Object.assign(locator.style,{
      left:(Math.min(...trackXs)).toFixed(1)+'px',top:(Math.min(...trackYs)).toFixed(1)+'px',
      width:Math.max(1,Math.max(...trackXs)-Math.min(...trackXs)).toFixed(1)+'px',
      height:Math.max(1,Math.max(...trackYs)-Math.min(...trackYs)).toFixed(1)+'px'
   });}
   if(p<.245){gate.style.opacity='0';return;}
   // A geometric quad tracks the monitor through the zoom; opening grows from
   // exactly those four projected corners. The retreat is scroll-reversible.
   const growth=smooth(.315,.49,p);
   const corners=[[-W*.025,-H*.045],[W*1.025,-H*.045],[W*1.025,H*1.045],[-W*.025,H*1.045]];
   const poly=transformed.map(([x,y],i)=>[mix(x,corners[i][0],growth),mix(y,corners[i][1],growth)]);
   gate.style.clipPath=`polygon(${poly.map(([x,y])=>`${x.toFixed(1)}px ${y.toFixed(1)}px`).join(',')})`;
   const minX=Math.min(...poly.map(v=>v[0])),maxX=Math.max(...poly.map(v=>v[0]));
   const minY=Math.min(...poly.map(v=>v[1])),maxY=Math.max(...poly.map(v=>v[1]));
   Object.assign(surface.style,{left:minX.toFixed(1)+'px',top:minY.toFixed(1)+'px',width:Math.max(1,maxX-minX).toFixed(1)+'px',height:Math.max(1,maxY-minY).toFixed(1)+'px'});
   gate.style.opacity=phase(.275,.324,.59,.665,p).toFixed(3);
   xray.style.opacity=smooth(.348,.456,p).toFixed(3);
   // Move through the X-ray plane before entering the tunnel, not a static wipe.
   xray.style.transform=`scale(${mix(1,1.42,smooth(.400,.550,p)).toFixed(3)})`;
   xray.style.filter=`brightness(${mix(.86,.60,smooth(.450,.550,p)).toFixed(3)}) saturate(.78) blur(${mix(0,2.5,smooth(.45,.55,p)).toFixed(2)}px)`;
   surface.style.opacity=(1-smooth(.445,.575,p)).toFixed(3);
   const t=smooth(.418,.615,p), showDepth=phase(.401,.468,.565,.639,p);
   depth.style.opacity=showDepth.toFixed(3);
   grid.style.transform=`perspective(800px) rotateX(${mix(70,57,t).toFixed(2)}deg) translateY(${(-t*11).toFixed(1)}%) scale(${(1.26+t*.68).toFixed(3)})`;
   slices.forEach((el,i)=>{
      const advance=i*.19+t*1.25;
      const size=.18+advance*.92;
      el.style.transform=`translate(-50%,-50%) scale(${size.toFixed(3)}) rotate(${((i-2)*1.9).toFixed(2)}deg)`;
      el.style.opacity=(showDepth*(.21+.57*Math.sin(Math.PI*Math.min(1,advance*.72)))).toFixed(3);
   });
   ray.style.transform=`translateX(${(-56+t*99).toFixed(1)}%)`;
   // Keep the 2D core ONLY as an early cue / GLB-unavailable fallback.
   // Once the real model starts appearing, the flat image yields its place;
   // there is no silhouette -> giant fully solid model switch.
   const modelLive=!!document.body?.classList?.contains('model-live');
   const coreAlpha=phase(.462,.510,.55,.635,p) * (modelLive ? 1-smooth(.493,.571,p) : 1);
   core.style.opacity=coreAlpha.toFixed(3);
   core.style.transform=`translate(-50%,-50%) scale(${mix(.50,1.06,smooth(.472,.608,p)).toFixed(3)})`;
 }
 window.TAQWEEMPortal={update,camera,calibration:'treatment 1280x720 monitor quad; responsive object-fit cover mapping'};
})();
