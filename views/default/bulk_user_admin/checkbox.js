define(function(require) {
    var elgg = require("elgg");
    var $ = require("jquery");

    $("#checkAll").click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
});
