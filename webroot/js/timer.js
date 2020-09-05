function dateCounter() {

    var timer = setInterval(function () {
        //現在の日時取得
        var nowDate = new Date();
        //終了の日時取得
        var anyDate = new Date(json_data.endtime);
        //日数を計算
        var daysBetween = Math.floor((anyDate - nowDate) / (1000 * 60 * 60 * 24));
        var ms = (anyDate - nowDate);
        if (ms >= 0) {
            //時間を取得
            var h = Math.floor(ms / 3600000);
            var _h = h % 24;
            //分を取得
            var m = Math.floor((ms - h * 3600000) / 60000);
            //秒を取得
            var s = Math.floor((ms - h * 3600000 - m * 60000) / 1000);

            //HTML上に出力
            document.getElementById("countdown").innerHTML = "終了まで " + daysBetween + "日" + _h + "時間" + m + "分" + s + "秒";

            if ((h == 0) && (m == 0) && (s == 0)) {
                clearInterval(timer);
                document.getElementById("countdown").innerHTML = "終了しました";
            }
        } else {
            document.getElementById("countdown").innerHTML = "終了しました";
        }
    }, 1000);
}
dateCounter();
