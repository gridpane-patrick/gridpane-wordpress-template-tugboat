!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=5)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wp.i18n},function(e,t){e.exports=window.wp.htmlEntities},function(e,t){e.exports=window.wc.wcBlocksRegistry},function(e,t){e.exports=window.wc.wcSettings},function(e,t,n){"use strict";n.r(t);var r,o=n(0),c=n(1),i=n(3),a=n(4),u=n(2);const l=Object(a.getSetting)("fawaterak_data",null),d=()=>Object(o.createElement)("div",null,Object(u.decodeEntities)(l.description||"")),s={name:"fawaterak",label:Object(o.createElement)(o.Fragment,null,Object(u.decodeEntities)(Object(c.__)("Fawaterak","woo-gutenberg-products-block")),Object(o.createElement)("img",{src:scriptVars.imageUrl,alt:Object(u.decodeEntities)(l.title||Object(c.__)("Fawaterak","woo-gutenberg-products-block"))})),placeOrderButtonLabel:Object(c.__)("Proceed to Fawatrak","woo-gutenberg-products-block"),content:Object(o.createElement)(d,null),edit:Object(o.createElement)(d,null),canMakePayment:()=>!0,ariaLabel:Object(u.decodeEntities)(l.title||Object(c.__)("Payment via Fawaterak","woo-gutenberg-products-block")),supports:{features:null!==(r=l.supports)&&void 0!==r?r:[]}};Object(i.registerPaymentMethod)(s)}]);