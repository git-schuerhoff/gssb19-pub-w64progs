Accordion=Class.create();Accordion.prototype={initialize:function(n,t,i){this.container=$(n);this.checkAllow=i||!1;this.disallowAccessToNextSections=!1;this.sections=$$("#"+n+" .section");this.currentSection=!1;var r=$$("#"+n+" .section "+t);r.each(function(n){Event.observe(n,"click",this.sectionClicked.bindAsEventListener(this))}.bind(this))},sectionClicked:function(n){this.openSection($(Event.element(n)).up(".section"));Event.stop(n)},openSection:function(n){var n=$(n),r,i,t;if((!this.checkAllow||Element.hasClassName(n,"allow"))&&n.id!=this.currentSection&&(this.closeExistingSection(),this.currentSection=n.id,$(this.currentSection).addClassName("active"),r=Element.select(n,".a-item"),r[0].show(),this.disallowAccessToNextSections))for(i=!1,t=0;t<this.sections.length;t++)i&&Element.removeClassName(this.sections[t],"allow"),this.sections[t].id==n.id&&(i=!0)},closeSection:function(n){$(n).removeClassName("active");var t=Element.select(n,".a-item");t[0].hide()},openNextSection:function(n){for(section in this.sections){var t=parseInt(section)+1;if(this.sections[section].id==this.currentSection&&this.sections[t]){n&&Element.addClassName(this.sections[t],"allow");this.openSection(this.sections[t]);return}}},openPrevSection:function(n){for(section in this.sections){var t=parseInt(section)-1;if(this.sections[section].id==this.currentSection&&this.sections[t]){n&&Element.addClassName(this.sections[t],"allow");this.openSection(this.sections[t]);return}}},closeExistingSection:function(){this.currentSection&&this.closeSection(this.currentSection)}}