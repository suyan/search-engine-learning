<?php
ini_set('memory_limit', '1000M');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query) {
    require_once('php/phpSolrClient/Service.php');
    require_once('php/SpellCorrector.php');
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572hw4/');
    $corrector = SpellCorrector::correct($query);
    if ($corrector == $query) {
        $corrector = "";
    }
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
    $param = [
        'q.op' => 'AND',
    ];
    if (array_key_exists("pagerank", $_REQUEST)) {
        $param['sort'] ="pagerank.txt desc";
    }
    $results = $solr->search($query, 0, $limit, $param);

    // read mapping from mapping.csv
    $file = fopen("mapping.csv", "r");
    $mappings = [];
    while(!feof($file)){
        $line = fgets($file);
        $names = explode(",", trim($line));
        if (isset($names[1])) {
            $mappings[$names[0]] = $names[1];
        }
    }
    fclose($file);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Solr Client</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
</head>
<body>
    <form accept-charset="utf-8" method="get">
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input type="submit"/>
        <br>
        <input type="checkbox" name="pagerank">Use Page Rank Algorhthm<br>
    </form>
    <?php if ($results): ?>
        <?php 
            $total = (int)$results->response->numFound;
            $start = min(1, $total);
            $end = min($limit, $total);
        ?>
        <?php if ($corrector != ""): ?>
            <p>Do you want to search: <a href="http://localhost/index.php?q=<?php echo $corrector; ?>"><?php echo $corrector; ?></a></p>
        <?php endif; ?>
        <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
        <hr>
        <?php foreach ($results->response->docs as $doc): ?>
            <?php 
                $id = $doc->id;
                $url = $mappings[$id];
            ?>
            <a href="<?php echo $url; ?>">Document</a>
            <?php echo $doc->title ? $doc->title : "None"; ?>
            <p>
                Author: <?php echo $doc->author ? $doc->author : "None"; ?> | Size: <?php echo $doc->stream_size ? $doc->stream_size : "None"; ?>
            </p>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        $(function() {
            var URL_PREFIX = "http://localhost:8983/solr/csci572hw4/suggest?q=";
            var URL_SUFFIX = "&wt=json";
            $("#q").autocomplete({
                source : function(request, response) {
                    var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                    var URL = URL_PREFIX + lastword + URL_SUFFIX;
                    $.ajax({
                        url : URL,
                        success : function(data) {
                            var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                            var suggestions = data.suggest.suggest[lastword].suggestions;
                            suggestions = $.map(suggestions, function (value, index) {
                                var prefix = "";
                                var query = $("#q").val();
                                var queries = query.split(" ");
                                if (queries.length > 1) {
                                    var lastIndex = query.lastIndexOf(" ");
                                    prefix = query.substring(0, lastIndex + 1).toLowerCase();
                                }
                                if (prefix == "" && isStopWord(value.term)) {
                                    return null;
                                }
                                if (!/^[0-9a-zA-Z]+$/.test(value.term)) {
                                    return null;
                                }
                                return prefix + value.term;
                            });
                            response(suggestions.slice(0, 5));
                        },
                        dataType : 'jsonp',
                        jsonp : 'json.wrf'
                    });
                },
                minLength : 1
            });
        });
        function isStopWord(word)
        {
            var regex = new RegExp("\\b"+word+"\\b","i");
            return stopWords.search(regex) < 0 ? false : true;
        }

        var stopWords = "a,able,about,above,abst,accordance,according,accordingly,across,act,actually,added,adj,\
        affected,affecting,affects,after,afterwards,again,against,ah,all,almost,alone,along,already,also,although,\
        always,am,among,amongst,an,and,announce,another,any,anybody,anyhow,anymore,anyone,anything,anyway,anyways,\
        anywhere,apparently,approximately,are,aren,arent,arise,around,as,aside,ask,asking,at,auth,available,away,awfully,\
        b,back,be,became,because,become,becomes,becoming,been,before,beforehand,begin,beginning,beginnings,begins,behind,\
        being,believe,below,beside,besides,between,beyond,biol,both,brief,briefly,but,by,c,ca,came,can,cannot,can't,cause,causes,\
        certain,certainly,co,com,come,comes,contain,containing,contains,could,couldnt,d,date,did,didn't,different,do,does,doesn't,\
        doing,done,don't,down,downwards,due,during,e,each,ed,edu,effect,eg,eight,eighty,either,else,elsewhere,end,ending,enough,\
        especially,et,et-al,etc,even,ever,every,everybody,everyone,everything,everywhere,ex,except,f,far,few,ff,fifth,first,five,fix,\
        followed,following,follows,for,former,formerly,forth,found,four,from,further,furthermore,g,gave,get,gets,getting,give,given,gives,\
        giving,go,goes,gone,got,gotten,h,had,happens,hardly,has,hasn't,have,haven't,having,he,hed,hence,her,here,hereafter,hereby,herein,\
        heres,hereupon,hers,herself,hes,hi,hid,him,himself,his,hither,home,how,howbeit,however,hundred,i,id,ie,if,i'll,im,immediate,\
        immediately,importance,important,in,inc,indeed,index,information,instead,into,invention,inward,is,isn't,it,itd,it'll,its,itself,\
        i've,j,just,k,keep,keeps,kept,kg,km,know,known,knows,l,largely,last,lately,later,latter,latterly,least,less,lest,let,lets,like,\
        liked,likely,line,little,'ll,look,looking,looks,ltd,m,made,mainly,make,makes,many,may,maybe,me,mean,means,meantime,meanwhile,\
        merely,mg,might,million,miss,ml,more,moreover,most,mostly,mr,mrs,much,mug,must,my,myself,n,na,name,namely,nay,nd,near,nearly,\
        necessarily,necessary,need,needs,neither,never,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,nor,\
        normally,nos,not,noted,nothing,now,nowhere,o,obtain,obtained,obviously,of,off,often,oh,ok,okay,old,omitted,on,once,one,ones,\
        only,onto,or,ord,other,others,otherwise,ought,our,ours,ourselves,out,outside,over,overall,owing,own,p,page,pages,part,\
        particular,particularly,past,per,perhaps,placed,please,plus,poorly,possible,possibly,potentially,pp,predominantly,present,\
        previously,primarily,probably,promptly,proud,provides,put,q,que,quickly,quite,qv,r,ran,rather,rd,re,readily,really,recent,\
        recently,ref,refs,regarding,regardless,regards,related,relatively,research,respectively,resulted,resulting,results,right,run,s,\
        said,same,saw,say,saying,says,sec,section,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sent,seven,several,shall,she,shed,\
        she'll,shes,should,shouldn't,show,showed,shown,showns,shows,significant,significantly,similar,similarly,since,six,slightly,so,\
        some,somebody,somehow,someone,somethan,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specifically,specified,specify,\
        specifying,still,stop,strongly,sub,substantially,successfully,such,sufficiently,suggest,sup,sure,t,take,taken,taking,tell,tends,\
        th,than,thank,thanks,thanx,that,that'll,thats,that've,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,\
        thered,therefore,therein,there'll,thereof,therere,theres,thereto,thereupon,there've,these,they,theyd,they'll,theyre,they've,\
        think,this,those,thou,though,thoughh,thousand,throug,through,throughout,thru,thus,til,tip,to,together,too,took,toward,towards,\
        tried,tries,truly,try,trying,ts,twice,two,u,un,under,unfortunately,unless,unlike,unlikely,until,unto,up,upon,ups,us,use,used,\
        useful,usefully,usefulness,uses,using,usually,v,value,various,'ve,very,via,viz,vol,vols,vs,w,want,wants,was,wasn't,way,we,wed,\
        welcome,we'll,went,were,weren't,we've,what,whatever,what'll,whats,when,whence,whenever,where,whereafter,whereas,whereby,wherein,\
        wheres,whereupon,wherever,whether,which,while,whim,whither,who,whod,whoever,whole,who'll,whom,whomever,whos,whose,why,widely,\
        willing,wish,with,within,without,won't,words,world,would,wouldn't,www,x,y,yes,yet,you,youd,you'll,your,youre,yours,yourself,\
        yourselves,you've,z,zero";
    </script>
</body>
</html>