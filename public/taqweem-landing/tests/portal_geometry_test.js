/* Local geometry regression, no browser or external packages required. */
const vm=require('node:vm');
const fs=require('node:fs');
const assert=require('node:assert/strict');
const script=fs.readFileSync(__dirname+'/../js/portal.js','utf8');
const smooth=(a,b,p)=>{const t=Math.min(1,Math.max(0,(p-a)/(b-a)));return t*t*(3-2*t)};
function test(w,h){
  const elements={};
  function el(id){return elements[id]??(elements[id]={style:{},clientWidth:w,clientHeight:h});}
  const document={getElementById:el,querySelector:()=>el('locator'),querySelectorAll:()=>Array.from({length:5},(_,i)=>el('slice'+i))};
  const window={};vm.runInNewContext(script,{document,window});
  assert(window.TAQWEEMPortal);
  const {camera,update}=window.TAQWEEMPortal;
  const m=smooth(.18,.40,.49),c=camera(m);update(.49,m,c);
  const full=el('portal-gateway').style.clipPath;
  assert(full.includes('polygon('));
  assert.equal(el('portal-gateway').style.opacity,'1.000');
  const styleAt49={...el('portal-gateway').style};
  update(.33,smooth(.18,.40,.33),camera(smooth(.18,.40,.33)));
  update(.49,m,c);
  assert.deepEqual(el('portal-gateway').style,styleAt49,'Reverse scroll must reproduce original projection');
  assert.equal(camera(0).posX,w<=600?.54:.5);
  assert.equal(camera(1).posX,w<=600?.85:.5);
  for(const p of [.16,.24,.32,.42,.53,.62,.73]){
    const m=smooth(.18,.40,p);update(p,m,camera(m));
    assert(!String(el('portal-gateway').style.opacity).includes('NaN'));
  }
  console.log(`PASS geometry + forward/reverse determinism at ${w}x${h}`);
}
test(1440,900);test(390,844);test(320,700);
