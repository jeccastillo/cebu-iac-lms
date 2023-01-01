// const api_url = "http://103.225.39.201:8081/api/v1/";
// const api_url = "http://103.225.39.201:8081/api/v1/";
const api_url = "http://103.225.39.199/api/v1/";
const base_url = "http://103.225.39.200/cebu-iac-lms/";

const api_url_article = "http://103.225.39.201:8081/api/v1/";
// const api_url_article = "http://172.16.80.22:8081/api/v1/"

function load_schedule(sched){
    for(i in sched){
                
        let day = sched[i].strDay;
        let text = sched[i].strCode;
        let hourspan = sched[i].hourdiff * 2;
        let st = sched[i].st;
        
        $("#"+st+" :nth-child("+day+")").addClass("bg-teal");
        $("#"+st+" :nth-child("+day+")").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
        $("#"+st+" :nth-child("+day+")").html("<div style='text-align:center;'>"+text+"</div>");
        nxt = $("#"+st);
        nxt.next().children(":nth-child("+day+")").html("<div style='text-align:center;'></div>");
        for(i=1;i<hourspan;i++){                    
            nxt.next().children(":nth-child("+day+")").addClass("bg-teal");
            if(i==hourspan-1)
            nxt.next().children(":nth-child("+day+")").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            else
                nxt.next().children(":nth-child("+day+")").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
            
            nxt = nxt.next();
        }
        $("#sched-table").val($("#sched-table-container").html());                                                        
    }
}
