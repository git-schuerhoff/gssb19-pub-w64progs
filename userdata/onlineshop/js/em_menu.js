/**
 * EM MegaMenuPro
 *
 * @license commercial software
 * @copyright (c) 2012 Codespot Software JSC - EMThemes.com. (http://www.emthemes.com)
 */
(function (n) {
    function t() {
        return n("body").hasClass("adapt-0")
    }

    function u() {
        function u() {
            var s, e, u, o, r;
            if (t()) {
                if (f) {
                    f = !1;
                    return
                }
                if (location.hash && (s = location.hash.match(/^#menu\/(.*)$/))) {
                    if (e = s[1].split("/"), e.length == 0) return;
                    if (r = n(".em_nav").eq(e.shift()).find(".hnav, .vnav"), u = r, u.length > 0) {
                        for (n(".mhover", r).removeClass("mhover"), o = 0; e.length > 0;) u = u.children().eq(e.shift()), (u.hasClass("menu-item-link") || u.get(0).tagName == "LI" && u.parents(".em-catalog-navigation").length > 0) && (u.addClass("mhover"), o -= r.width());
                        o = 0;
                        r.animate({"margin-left": o + "px"}, i)
                    }
                } else location.hash || (r = n(".em_nav .hnav, .em_nav .vnav"), n(".mhover", r).removeClass("mhover"), r.animate({"margin-left": ""}, i))
            }
        }

        var f = !1;
        u();
        n(window).hashchange(u);
        n(window).bind("emadaptchange", function () {
            t() ? u() : n(".em_nav .hnav, .em_nav .vnav").css("margin-left", "").find(".mhover").removeClass("mhover")
        });
        n(".em_nav").each(function (i) {
            var u = n(".hnav, .vnav", this);
            n(".em-catalog-navigation li.parent, .menu-item-link.menu-item-parent", u).each(function () {
                n(this).prepend('<a href="#" class="arrow"><span>&gt;<\/span><\/a>')
            });
            n("a.arrow", u).bind(r ? "click mouseenter" : "click", function (r) {
                var f, s, e, o;
                t() && ((r.preventDefault(), r.stopPropagation(), f = n(this.parentNode), f.hasClass("mhover")) || (s = f.children(".menu-container"), e = s.attr("id").replace(/submenucont_/g, ""), n("#submenu_" + e).html() == "" && n.ajax({
                    url: "submenu_"+g_lngID+"_" + e + ".html",
                    type: "get",
                    async: !0,
                    success: function (t) {
                        n("#submenu_" + e).html(t)
                    }
                }), r.which != 0 && (r.screenX != 0 || r.screenY != 0)) && (n(".mhover", u).not(f.parents(".mhover")).removeClass("mhover"), f.addClass("mhover"), o = [f.index()], f.parentsUntil(u).each(function () {
                    o.unshift(n(this).index())
                }), location = "#menu/" + i + "/" + o.join("/")))
            });
            n("a.gs-nav-main-item", u).bind(r ? "click mouseenter" : "click", function (r) {
                var f, e;
                t() && ((r.preventDefault(), r.stopPropagation(), f = n(this.parentNode), f.hasClass("mhover")) || r.which != 0 && (r.screenX != 0 || r.screenY != 0) && (n(".mhover", u).not(f.parents(".mhover")).removeClass("mhover"), f.addClass("mhover"), e = [f.index()], f.parentsUntil(u).each(function () {
                    e.unshift(n(this).index())
                }), location = "#menu/" + i + "/" + e.join("/")))
            })
        })
    }

    function f() {
        function t(n, t) {
            var i = t.offset().left + t.outerWidth() - (n.offset().left + n.outerWidth()),
                r = n.offset().left + i - t.offset().left;
            r < 0 && (i = i - r);
            i < 0 && n.css("left", i + "px")
        }

        n(".em_nav > .hnav > .menu-item-link > .menu-container").parent().hover(function () {
            var i = n(this).children(".menu-container"), r = i.attr("id").replace(/submenucont_/g, ""), u;
            n("#submenu_" + r).html() == "" && n.ajax({
                url: "submenu_"+g_lngID+"_" + r + ".html",
                type: "get",
                async: !0,
                success: function (t) {
                    n("#submenu_" + r).html(t)
                }
            });
            n(this).hasClass("menu-item-depth-0") && i.css("left", 0);
            u = n(this).parents(".em_nav").first();
            t(i, u)
        }, function () {
            n(this).children(".menu-container").css("left", "")
        })
    }

    function e() {
        n(".vnav ul.level0,.vnav > .menu-item-link > .menu-container").parent().mouseover(function () {
            var t = n(this).children(".menu-container,ul.level0"), i = t.attr("id").replace(/submenucont_/g, ""), o;
            n("#submenu_" + i).html() == "" && n.ajax({
                url: "submenu_"+g_lngID+"_" + i + ".html",
                type: "get",
                async: !0,
                success: function (t) {
                    n("#submenu_" + i).html(t)
                }
            });
            var r = t.outerHeight(), e = t.offset().top, u = n(window).height(), f = r + e;
            f >= u ? ($setTop = n(this).parent().offset().top - n(this).offset().top - 10, $setTop + r < n(this).height() && ($setTop = n(this).outerHeight() - r)) : $setTop = -1;
            o = n(this).parents(".em_nav").first().parents().first();
            t.css("top", $setTop);
            f < u && n(".vnav ul.level0,.vnav > .menu-item-link > .menu-container").first().css("top", $setTop - 9 + "px")
        });
        n(".vnav .menu-item-link > .menu-container,.vnav ul.level0").parent().mouseout(function () {
            var t = n(this).children(".menu-container,ul.level0");
            t.removeAttr("style")
        })
    }

    var i = "normal", r = /iPhone|iPod|iPad|Phone|Mobile|Android|hpwos/i.test(navigator.userAgent),
        o = /iPhone|iPod|Phone|Android/i.test(navigator.userAgent);
    n(document).ready(function () {
        f();
        e();
        u()
    })
})(jQuery)