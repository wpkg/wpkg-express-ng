/*
 * WPKG Express NG js
 */
function updateChecks() {
	$("#checks a[href*=\"move\"]").click(function() {
      	$("#checks").load(this.href, function() {updateChecks();});
      	return false;
    });
    $("#checks a[href*=\"delete\"]").click(function() {
        if ($(this).parent().clone().children().remove().end().text().indexOf("Logical") == 0) {
            if (!confirm("Are you sure you wish to delete this package check?\nAny children of this logical check will also be removed."))
				return false;
            }
            $("#checks").load(this.href, function() {updateChecks();});
            return false;
        });
}
function updateActions() {
    $("#actions a[href*=\"move\"]").click(function() {
        $("#actions").load(this.href, function() {updateActions();});
        return false;
    });
    $("#actions a[href*=\"delete\"]").click(function() {
        $("#actions").load(this.href, function() {updateActions();});
        return false;
    });
}
function updateVariables() {
    $("#variables a[href*=\"delete\"]").click(function() {
        $("#variables").load(this.href, function() {updateVariables();});
        return false;
    });
}
function updatePackages() {
    $("#packages a[href*=\"delete\"]").click(function() {
        $("#packages").load(this.href, function() {updatePackages();});
        return false;
    }); 
}
function updateProfiles() {
    $("#profiles a[href*=\"delete\"]").click(function() {
        $("#profiles").load(this.href, function() {updateProfiles();});
        return false;
    }); 
}
function prettyDates() {
	var interval;
    $(".date").each(function(){ this.title = this.innerHTML; }).prettyDate();
    clearInterval(interval);
    interval = setInterval(function(){ $(".date").prettyDate(); }, 5000);
}
function updatePagingLinks() {
	var params = "";
    $(".paging a").click(function(){ params=this.href.substr(this.href.indexOf("/page:")); $("#content").load(this.href, function() {update();}); return false; });
    $("th a").click(function(){ params=this.href.substr(this.href.indexOf("/page:")); $("#content").load(this.href, function() {update();}); return false; });
}
function updateOtherLinks() {
    $("a[href*=\"enable\"], a[href*=\"disable\"]").click(function() {
        $.ajax({
            domobj: this,
            url: this.href,
            cache: false,
            success: function(data, textStatus) {
                data = $.secureEvalJSON(data);
                if (data.success) {
                    if ($(this.domobj).html() == "Yes") {
                        $(this.domobj).attr("href", this.domobj.href.replace("disable", "enable"));
                        $(this.domobj).html("No");
                        $(this.domobj).parent().parent().attr("class", "disabled");
                    } else {
                        this.domobj.href = this.domobj.href.replace("enable", "disable");
                        $(this.domobj).html("Yes");
                        $(this.domobj).parent().parent().removeAttr("class");
                    }
                    var now = new Date();
                    $(this.domobj).parent().parent().children().eq(4).children().eq(0).attr("title", now.format("Y-m-d h:i:s A"));
                    if ($("#flashMessage").length > 0)
                        $("#flashMessage").remove();
                } else {
                    if ($("#flashMessage").length == 0)
                        $("#content h2:first").before("<div id=\"flashMessage\" class=\"message\"></div>");
                    $("#flashMessage").html(data.message);
                }
            }
        });
        return false;
    });
    $("a[alt=\"Delete\"]").click(function() {
        var type = $("#content h2").text();
        type = type.substr(0, type.indexOf(" ")-1);
        if (confirm("Are you sure you wish to delete the " + type + " \"" + $(this).parent().parent().children().eq(2).children().eq(0).html() + "\"?")) {
            $("#content").load(this.href + params, function() {update();});
        }
        return false;
    });
    $("#content a[href*=\"/move\"]").click(function() {
        $("#content").load(this.href + params, function() {update();});
        return false;
    });
}
function update() {
    updatePagingLinks();
    updateOtherLinks();
}
$(document).ready(function(){
    prettyDates();
    updateChecks();
    updateActions();
	updateVariables();
	updatePackages();
	updateProfiles();
	update();
});
