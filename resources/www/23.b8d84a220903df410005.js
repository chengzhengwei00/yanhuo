(window.webpackJsonp=window.webpackJsonp||[]).push([[23],{egSf:function(l,n,t){"use strict";t.r(n);var e=t("CcnG"),u=function(){function l(){}return l.prototype.transform=function(l,n){var t=[];return l.forEach(function(l){var n=l.contract_id;l.data.forEach(function(l){l.contract_id=n,t.push(l)})}),t},l}(),o=t("dlZq"),i=t("r0Ox"),c=function(){function l(l,n,t,e,u){this.activeRoute=l,this.router=n,this.baseData=t,this.accessPipe=e,this.taskService=u,this.accessList=[]}return l.prototype.ngOnInit=function(){var l=this;this.activeRoute.params.subscribe(function(n){l.task_id=n.tid,l.factory_group=n.fid}),this.activeRoute.data.subscribe(function(n){l.poList=n.poList,l.defaultSelect=n.poList[0],l.currentPoId=l.currentPoId?l.currentPoId:l.defaultSelect.contract_id,l.choicePo(),l.baseData.printDebug&&console.log(n)}),this.taskService.getAccessbyTaskAndFac({task_id:this.task_id,contract_id:this.currentPoId}).subscribe(function(n){l.accessList=l.accessPipe.transform(n),l.baseData.printDebug&&console.log(l.accessList)})},l.prototype.toInspection=function(l,n){"sku"==n?(this.router.navigate(["inspection/sku",this.task_id,l.sku,this.currentPoId]),sessionStorage.setItem("TASK_DETAIL_PROJECT",JSON.stringify(l))):(this.router.navigate(["inspection/acc",this.task_id,this.currentPoId]),sessionStorage.setItem("TASK_DETAIL_PROJECT",JSON.stringify(l)))},l.prototype.choicePo=function(){for(var l=this,n=0;n<this.poList.length;n++)this.poList[n].contract_id==this.currentPoId&&(this.defaultSelect=this.poList[n]);this.taskService.getSkuListByPoAndTask(this.task_id,this.currentPoId).subscribe(function(n){console.log(n),l.taskDetail=l.objToArrayByKey(n.data)})},l.prototype.objToArrayByKey=function(l){var n=[];for(var t in l){var e={};e[t]=l[t],e[t].sku=t,n.push(e[t])}return n},l}(),a=t("ZYCi"),d=function(){function l(l,n){this.taskService=l,this.Router=n}return l.prototype.resolve=function(l,n){return this.taskService.getPoListByTaskAndFactory({task_id:l.params.tid,factory_group:l.params.fid})},l.ngInjectableDef=e.defineInjectable({factory:function(){return new l(e.inject(o.a),e.inject(a.m))},token:l,providedIn:"root"}),l}(),r=function(){return function(){}}(),s=t("pMnS"),p=t("oBZk"),f=t("ZZ/e"),g=t("Ip0R"),h=t("gIcY"),m=e["\u0275crt"]({encapsulation:0,styles:[[".slider_box[_ngcontent-%COMP%]:after{content:'';display:block;clear:both}.slider_box[_ngcontent-%COMP%] > div[_ngcontent-%COMP%]{width:100%;padding:20px;box-sizing:border-box}.slider_box[_ngcontent-%COMP%] > div[_ngcontent-%COMP%]   .h4[_ngcontent-%COMP%]{text-align:center}.slider_box[_ngcontent-%COMP%]   .table[_ngcontent-%COMP%]{min-width:300px;color:#666}.slider_box[_ngcontent-%COMP%]   .table[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]{text-align:center;vertical-align:middle}.thumbnail[_ngcontent-%COMP%]{display:inline-block;margin-bottom:0;width:50px;height:auto;max-height:50px}ion-select[_ngcontent-%COMP%]{border:1px solid #ccc}"]],data:{}});function b(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,2,"ion-select-option",[],null,null,null,p.X,p.x)),e["\u0275did"](1,49152,null,0,f.mb,[e.ChangeDetectorRef,e.ElementRef],{value:[0,"value"]},null),(l()(),e["\u0275ted"](2,0,[" "," "]))],function(l,n){l(n,1,0,e["\u0275inlineInterpolate"](1,"",n.context.$implicit.contract_id,""))},function(l,n){l(n,2,0,n.context.$implicit.contract_no)})}function v(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,6,"tr",[],null,[[null,"click"]],function(l,n,t){var e=!0;return"click"===n&&(e=!1!==l.component.toInspection(l.context.$implicit,"sku")&&e),e},null,null)),(l()(),e["\u0275eld"](1,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275eld"](2,0,null,null,0,"img",[["alt",""],["class","thumbnail"]],[[8,"src",4]],null,null,null,null)),(l()(),e["\u0275eld"](3,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](4,null,["",""])),(l()(),e["\u0275eld"](5,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](6,null,["",""]))],null,function(l,n){l(n,2,0,n.component.baseData.fileUrl+n.context.$implicit.pic),l(n,4,0,n.context.$implicit.name),l(n,6,0,n.context.$implicit.sku)})}function C(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,1,"p",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u6682\u65e0\u914d\u4ef6"]))],null,null)}function k(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,6,"tr",[],null,[[null,"click"]],function(l,n,t){var e=!0;return"click"===n&&(e=!1!==l.component.toInspection(l.context.$implicit,"acc")&&e),e},null,null)),(l()(),e["\u0275eld"](1,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](2,null,[" "," "])),(l()(),e["\u0275eld"](3,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](4,null,["",""])),(l()(),e["\u0275eld"](5,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](6,null,["",""]))],null,function(l,n){l(n,2,0,n.context.$implicit.ProductCode),l(n,4,0,n.context.$implicit.AccessoryName),l(n,6,0,n.context.$implicit.AccessoryCode)})}function _(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,11,"table",[["class","table table-striped table-hover table-condensed table-bordered "]],null,null,null,null,null)),(l()(),e["\u0275eld"](1,0,null,null,7,"thead",[],null,null,null,null,null)),(l()(),e["\u0275eld"](2,0,null,null,6,"tr",[],null,null,null,null,null)),(l()(),e["\u0275eld"](3,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u5bf9\u5e94\u4ea7\u54c1sku"])),(l()(),e["\u0275eld"](5,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u4ea7\u54c1\u540d"])),(l()(),e["\u0275eld"](7,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["sku"])),(l()(),e["\u0275eld"](9,0,null,null,2,"tbody",[],null,null,null,null,null)),(l()(),e["\u0275and"](16777216,null,null,1,null,k)),e["\u0275did"](11,278528,null,0,g.NgForOf,[e.ViewContainerRef,e.TemplateRef,e.IterableDiffers],{ngForOf:[0,"ngForOf"]},null)],function(l,n){l(n,11,0,n.component.accessList)},null)}function I(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,6,"ion-header",[],null,null,null,p.M,p.l)),e["\u0275did"](1,49152,null,0,f.A,[e.ChangeDetectorRef,e.ElementRef],null,null),(l()(),e["\u0275eld"](2,0,null,0,4,"ion-toolbar",[],null,null,null,p.bb,p.A)),e["\u0275did"](3,49152,null,0,f.Ab,[e.ChangeDetectorRef,e.ElementRef],null,null),(l()(),e["\u0275eld"](4,0,null,0,2,"ion-title",[],null,null,null,p.ab,p.z)),e["\u0275did"](5,49152,null,0,f.yb,[e.ChangeDetectorRef,e.ElementRef],null,null),(l()(),e["\u0275ted"](-1,0,["\u4efb\u52a1\u8be6\u60c5"])),(l()(),e["\u0275eld"](7,0,null,null,33,"ion-content",[["padding",""]],null,null,null,p.K,p.j)),e["\u0275did"](8,49152,null,0,f.t,[e.ChangeDetectorRef,e.ElementRef],null,null),(l()(),e["\u0275eld"](9,0,null,0,8,"ion-select",[["cancelText","\u53d6\u6d88"],["interface","popover"],["okText","\u786e\u5b9a"],["value","currentPoId"]],[[2,"ng-untouched",null],[2,"ng-touched",null],[2,"ng-pristine",null],[2,"ng-dirty",null],[2,"ng-valid",null],[2,"ng-invalid",null],[2,"ng-pending",null]],[[null,"ngModelChange"],[null,"ionChange"],[null,"ionBlur"]],function(l,n,t){var u=!0,o=l.component;return"ionBlur"===n&&(u=!1!==e["\u0275nov"](l,10)._handleBlurEvent()&&u),"ionChange"===n&&(u=!1!==e["\u0275nov"](l,10)._handleChangeEvent(t.target.value)&&u),"ngModelChange"===n&&(u=!1!==(o.currentPoId=t)&&u),"ionChange"===n&&(u=!1!==o.choicePo()&&u),u},p.Y,p.w)),e["\u0275did"](10,16384,null,0,f.Lb,[e.ElementRef],null,null),e["\u0275prd"](1024,null,h.j,function(l){return[l]},[f.Lb]),e["\u0275did"](12,671744,null,0,h.o,[[8,null],[8,null],[8,null],[6,h.j]],{model:[0,"model"]},{update:"ngModelChange"}),e["\u0275prd"](2048,null,h.k,null,[h.o]),e["\u0275did"](14,16384,null,0,h.l,[[4,h.k]],null,null),e["\u0275did"](15,49152,null,0,f.lb,[e.ChangeDetectorRef,e.ElementRef],{cancelText:[0,"cancelText"],okText:[1,"okText"],selectedText:[2,"selectedText"],interface:[3,"interface"],interfaceOptions:[4,"interfaceOptions"],value:[5,"value"]},null),(l()(),e["\u0275and"](16777216,null,0,1,null,b)),e["\u0275did"](17,278528,null,0,g.NgForOf,[e.ViewContainerRef,e.TemplateRef,e.IterableDiffers],{ngForOf:[0,"ngForOf"]},null),(l()(),e["\u0275eld"](18,0,null,0,22,"div",[["class","slider_box"]],null,null,null,null,null)),(l()(),e["\u0275eld"](19,0,null,null,14,"div",[["class","sku-list "]],null,null,null,null,null)),(l()(),e["\u0275eld"](20,0,null,null,1,"h4",[["class",".h4 "]],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["sku\u5217\u8868"])),(l()(),e["\u0275eld"](22,0,null,null,11,"table",[["class","table table-striped table-hover table-condensed table-bordered "]],null,null,null,null,null)),(l()(),e["\u0275eld"](23,0,null,null,7,"thead",[],null,null,null,null,null)),(l()(),e["\u0275eld"](24,0,null,null,6,"tr",[],null,null,null,null,null)),(l()(),e["\u0275eld"](25,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u7f29\u7565\u56fe"])),(l()(),e["\u0275eld"](27,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u4ea7\u54c1\u540d"])),(l()(),e["\u0275eld"](29,0,null,null,1,"td",[],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["sku"])),(l()(),e["\u0275eld"](31,0,null,null,2,"tbody",[],null,null,null,null,null)),(l()(),e["\u0275and"](16777216,null,null,1,null,v)),e["\u0275did"](33,278528,null,0,g.NgForOf,[e.ViewContainerRef,e.TemplateRef,e.IterableDiffers],{ngForOf:[0,"ngForOf"]},null),(l()(),e["\u0275eld"](34,0,null,null,6,"div",[["class","acc-list"]],null,null,null,null,null)),(l()(),e["\u0275eld"](35,0,null,null,1,"h4",[["class",".h4"]],null,null,null,null,null)),(l()(),e["\u0275ted"](-1,null,["\u914d\u4ef6\u5217\u8868"])),(l()(),e["\u0275and"](16777216,null,null,1,null,C)),e["\u0275did"](38,16384,null,0,g.NgIf,[e.ViewContainerRef,e.TemplateRef],{ngIf:[0,"ngIf"]},null),(l()(),e["\u0275and"](16777216,null,null,1,null,_)),e["\u0275did"](40,16384,null,0,g.NgIf,[e.ViewContainerRef,e.TemplateRef],{ngIf:[0,"ngIf"]},null)],function(l,n){var t=n.component;l(n,12,0,t.currentPoId),l(n,15,0,"\u53d6\u6d88","\u786e\u5b9a",t.defaultSelect.contract_no,"popover",t.customAlertOptions,"currentPoId"),l(n,17,0,t.poList),l(n,33,0,t.taskDetail),l(n,38,0,t.accessList&&!t.accessList.length),l(n,40,0,t.accessList&&t.accessList.length)},function(l,n){l(n,9,0,e["\u0275nov"](n,14).ngClassUntouched,e["\u0275nov"](n,14).ngClassTouched,e["\u0275nov"](n,14).ngClassPristine,e["\u0275nov"](n,14).ngClassDirty,e["\u0275nov"](n,14).ngClassValid,e["\u0275nov"](n,14).ngClassInvalid,e["\u0275nov"](n,14).ngClassPending)})}function x(l){return e["\u0275vid"](0,[(l()(),e["\u0275eld"](0,0,null,null,2,"app-task-detail",[],null,null,null,I,m)),e["\u0275prd"](512,null,u,u,[]),e["\u0275did"](2,114688,null,0,c,[a.a,a.m,i.a,u,o.a],null,null)],function(l,n){l(n,2,0)},null)}var P=e["\u0275ccf"]("app-task-detail",c,x,{},{},[]);t.d(n,"TaskDetailPageModuleNgFactory",function(){return y});var y=e["\u0275cmf"](r,[],function(l){return e["\u0275mod"]([e["\u0275mpd"](512,e.ComponentFactoryResolver,e["\u0275CodegenComponentFactoryResolver"],[[8,[s.a,P]],[3,e.ComponentFactoryResolver],e.NgModuleRef]),e["\u0275mpd"](4608,g.NgLocalization,g.NgLocaleLocalization,[e.LOCALE_ID,[2,g["\u0275angular_packages_common_common_a"]]]),e["\u0275mpd"](4608,h.x,h.x,[]),e["\u0275mpd"](4608,f.b,f.b,[e.NgZone,e.ApplicationRef]),e["\u0275mpd"](4608,f.Gb,f.Gb,[f.b,e.ComponentFactoryResolver,e.Injector,g.DOCUMENT]),e["\u0275mpd"](4608,f.Jb,f.Jb,[f.b,e.ComponentFactoryResolver,e.Injector,g.DOCUMENT]),e["\u0275mpd"](1073742336,g.CommonModule,g.CommonModule,[]),e["\u0275mpd"](1073742336,h.u,h.u,[]),e["\u0275mpd"](1073742336,h.h,h.h,[]),e["\u0275mpd"](1073742336,f.Cb,f.Cb,[]),e["\u0275mpd"](1073742336,a.p,a.p,[[2,a.v],[2,a.m]]),e["\u0275mpd"](1073742336,r,r,[]),e["\u0275mpd"](1024,a.k,function(){return[[{path:"task-detail/:tid/:fid",resolve:{poList:d},component:c}]]},[])])})}}]);