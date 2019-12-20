/** layuiAdmin.std-v1.2.1 LPPL License By http://www.layui.com/admin/ */ ;
layui.define(function (e) {
    var a = layui.admin;
    layui.use(["admin", "carousel"], function () {
        var e = layui.$,
            a = (layui.admin, layui.carousel),
            l = layui.element,
            t = layui.device();
        e(".layadmin-carousel").each(function () {
            var l = e(this);
            a.render({
                elem: this,
                width: "100%",
                arrow: "none",
                interval: l.data("interval"),
                autoplay: l.data("autoplay") === !0,
                trigger: t.ios || t.android ? "click" : "hover",
                anim: l.data("anim")
            })
        }), l.render("progress")
    }), layui.use(["carousel", "echarts", "jquery"], function () {
        var $ = layui.jquery;
        $.post(home_url, {}, function (ret) {
            var e = layui.$,
                a = (layui.carousel, layui.echarts),
                l = [],
                t = [{
                    tooltip: {
                        trigger: "axis"
                    },
                    calculable: !0,
                    legend: {
                        data: ["用户增长", "效益"]
                    },
                    xAxis: [{
                        type: "category",
                        data: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"]
                    }],
                    yAxis: [{
                        type: "value",
                        name: "",
                        axisLabel: {
                            formatter: "{value} "
                        }
                    }],

                    series: ret.data
                }],
                i = e("#LAY-index-pagetwo").children("div"),
                n = function (e) {
                    l[e] = a.init(i[e], layui.echartsTheme), l[e].setOption(t[e]), window.onresize = l[e].resize
                };
            i[0] && n(0)
        })
    })

});