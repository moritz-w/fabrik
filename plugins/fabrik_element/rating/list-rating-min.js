/*! fabrik */
var FbRatingList=new Class({options:{userid:0,mode:"",formid:0},Implements:[Events,Options],initialize:function(a,b){b.element=a,this.setOptions(b),this.options.canRate!==!1&&"creator-rating"!==this.options.mode&&(this.col=$$("."+a),this.origRating={},this.col.each(function(a){var b=a.getElements(".starRating");b.each(function(c){c.addEvent("mouseover",function(){this.origRating[a.id]=c.getParent(".fabrik_element").getElement(".ratingMessage").innerHTML.toInt(),b.each(function(a){this._getRating(c)>=this._getRating(a)?Fabrik.bootstrapped?a.removeClass("icon-star-empty").addClass("icon-star"):a.src=this.options.insrc:Fabrik.bootstrapped?a.addClass("icon-star-empty").removeClass("icon-star"):a.src=this.options.insrc}.bind(this)),c.getParent(".fabrik_element").getElement(".ratingMessage").innerHTML=c.get("data-fabrik-rating")}.bind(this)),c.addEvent("mouseout",function(){b.each(function(b){this.origRating[a.id]>=this._getRating(b)?Fabrik.bootstrapped?b.removeClass("icon-star-empty").addClass("icon-star"):b.src=this.options.insrc:Fabrik.bootstrapped?b.addClass("icon-star-empty").removeClass("icon-star"):b.src=this.options.insrc}.bind(this)),c.getParent(".fabrik_element").getElement(".ratingMessage").innerHTML=this.origRating[a.id]}.bind(this))}.bind(this)),b.each(function(a){a.addEvent("click",function(b){this.doAjax(b,a)}.bind(this))}.bind(this))}.bind(this)))},_getRating:function(a){var b=a.get("data-fabrik-rating");return b.toInt()},doAjax:function(a,b){a.stop(),this.rating=this._getRating(b);var c=b.getParent(".fabrik_element").getElement(".ratingMessage");Fabrik.loader.start(c);var d=new Element("div",{id:"starRatingCover",styles:{bottom:0,top:0,right:0,left:0,position:"absolute",cursor:"progress"}}),e=b.getParent(".fabrik_element").getElement("div");e.grab(d,"top");var f=document.id(b).getParent(".fabrik_row"),g=f.id.replace("list_"+this.options.listRef+"_row_",""),h={option:"com_fabrik",format:"raw",task:"plugin.pluginAjax",plugin:"rating",g:"element",method:"ajax_rate",formid:this.options.formid,element_id:this.options.elid,row_id:g,elementname:this.options.elid,userid:this.options.userid,rating:this.rating,mode:this.options.mode};new Request({url:"",data:h,onComplete:function(a){a=a.toInt(),this.rating=a,c.set("html",this.rating),Fabrik.loader.stop(c);var d=Fabrik.bootstrapped?"i":"img";b.getParent(".fabrik_element").getElements(d).each(function(b,c){a>c?Fabrik.bootstrapped?b.removeClass("icon-star-empty").addClass("icon-star"):b.src=this.options.insrc:Fabrik.bootstrapped?b.addClass("icon-star-empty").removeClass("icon-star"):b.src=this.options.insrc}.bind(this)),document.id("starRatingCover").destroy()}.bind(this)}).send()}});