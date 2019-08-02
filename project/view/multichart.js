// define topics
function defineTopics(item, index) {
    if (urlParams.has('topic')) {
        if (urlParams.get('topic') == 'file') {
            console.log('topic = file >> topics = topicsarray');
            topics = topicsarray;
        }
        else {
            console.log('topics from url');
            topics = [urlParams.get('topic')];
            Object.keys(data['dashboards']).forEach(checkJSONTopics);
        }
    }
    else {
        let topicerror = 'No topic set. Use default topics.';
        console.log(topicerror);
        alert(topicerror);
        topics = topicsarray;
    }   
}

// checkJSONTopics
function checkJSONTopics(item, index) {
    if (urlParams.get('topic') == Object.keys(data['dashboards'])[index]) {
        let itopic = Object.keys(data['dashboards'])[index];
        topics = data['dashboards'][itopic]['topics'];
    }
}

// writeUrl
function writeUrl(item, index) {
    url[item] = urlslug;
}

// get url parameter
function getUrlParams(item, index) {
    url[item] += '&topic=' + item;
    if (urlParams.has('start')) {
        var start = urlParams.get('start');
        url[item] += '&start=' + start;
    }
    if (urlParams.has('end')) {
        var end = urlParams.get('end');
        url[item] += '&end=' + end;
    }
    if (urlParams.has('unit')) {
        unit = urlParams.get('unit');
        url[item] += '&unit=' + unit;
    }
    if (urlParams.has('topic_b')) {
        topic_b = urlParams.get('topic_b');
        url[item] += '&topic_b=' + topic_b;
    }
    if (urlParams.has('topic_c')) {
        topic_c = urlParams.get('topic_c');
        url[item] += '&topic_c=' + topic_c;
    }
    // hidetotal - set false to display total
    if (urlParams.has('hidetotal')) {
        if (urlParams.get('hidetotal') == 'false') {
            hidetotal = false;
        }
    }
}

// add div and cancas
function addTopicDiv(item, index) {
    // container
    if (topics.length == 1) {
        document.getElementById('container').classList.add('uk-child-width-1-1@l');        
    }
    else if (topics.length > 2) {
        document.getElementById('container').classList.add('uk-child-width-1-3@l', 'uk-child-width-1-2@m', 'uk-child-width-1-1@s');
    }
    // cardbox
    var cardbox = document.createElement('div');
    cardbox.id = 'cardbox' + item;
    document.getElementById('container').appendChild(cardbox);
    // card
    var card = document.createElement('div');
    card.id = item;
    card.classList.add('uk-card', 'uk-card-default', 'uk-background-default');
    document.getElementById('cardbox' + item).appendChild(card);
    // card header
    var cardheader = document.createElement('div');
    cardheader.id = 'cardheader' + item;
    cardheader.classList.add('uk-card-header', 'uk-padding-small')
    document.getElementById(item).appendChild(cardheader);
    // card title
    var cardtitle = document.createElement('h4');
    cardtitle.id = 'cardtitle' + item;
    cardtitle.classList.add('uk-text-uppercase', 'uk-h4', 'uk-margin-remove-bottom') // , 'uk-card-title'
    cardtitle.textContent = item;
    document.getElementById('cardheader' + item).appendChild(cardtitle);
    // div#infotopic
    var info = document.createElement('p');
    info.id = 'info' + item;
    info.classList.add('uk-text-meta', 'uk-margin-remove-top');
    document.getElementById('cardheader' + item).appendChild(info);
    // card body
    var cardbody = document.createElement('div');
    cardbody.id = 'cardbody' + item;
    cardbody.classList.add('uk-card-body', 'uk-padding-small');
    cardbody.style.minHeight = "360px"; 
    cardbody.innerHTML = '<div id="spinner' + item + '" class="spinner" uk-spinner="ratio: 3"></div>';
    document.getElementById(item).appendChild(cardbody);
    // canvas#charttopic
    var canvas = document.createElement('canvas');
    canvas.id = 'chart' + item;
    document.getElementById('cardbody' + item).appendChild(canvas);
    // spinner
    var spinner = document.createElement('div');
    spinner.setAttribute('uk-spinner', 'ratio: 3');
    document.getElementById('cardbody' + item).appendChild(canvas);
    // card footer
    var cardfooter = document.createElement('div');
    cardfooter.id = 'cardfooter' + item;
    cardfooter.classList.add('uk-card-footer');
    document.getElementById(item).appendChild(cardfooter);
}

let xhr = [];
let obj = [];
function xmlHr(item, index) {
    xhr[item] = new XMLHttpRequest();
    xhr[item].open("GET", url[item], true);
    xhr[item].responseType = "json";
    xhr[item].send();
    xhr[item].onload = function displayContent(fxhr) {
        // console.log(this.response);
        if (xhr[item].status != 200) { // analyze HTTP status of the response
            let xhrerror = 'Error ' + xhr[item].status + ' ' + xhr[item].statusText;
            console.log(xhrerror);
            alert(xhrerror);
        }
        if (fxhr.target.responseType === 'json') {
            // console.log('json ' + item);
            document.getElementById('spinner' + item).style.display = "none";
            obj[item] = xhr[item].response;
            document.getElementById('cardtitle' + item).textContent = obj[item]['meta']['topic_comb'];
            // if there's time, make displayMeta - obj[item]['result'].forEach(displayMeta);
            document.getElementById('info' + item).innerHTML += 
                // ' Topic: '      + obj[item]['meta']['topic_comb'] + ', ' +
                // 'Count: '    + obj[item]['meta']['timeunits_count'] + ', ' +
                // 'Sum: '      + obj[item]['result']['sum'] + ', ' +
                // 'Mean: '     + obj[item]['result']['mean'] + ', ' +
                // 'Median: '   + obj[item]['result']['median'] + ', ' +
                // 'Timeunit: ' + obj[item]['meta']['timeunit'] +
                // '<br>' +
                // 'Log: '      + obj[item]['meta']['errors'] +
                // 'Loadtime: ' + obj[item]['meta']['loadtime'] +
                // '<ul>' +
                // ' <li>Artikelanzahl  ' + obj[item]['result']['sum'] + '</li>' +
                // ' <li>Prozentanteil  ' + obj[item]['result']['percent_mean'] + '</li>' +
                // ' <li>Sentiment      ' + obj[item]['result']['senti'] + '</li>' +
                // ' <li>Sentiment Mean ' + obj[item]['result']['senti_mean'] + '</li>' +
                // '</ul>' +
                '<table><!--  class="uk-table uk-table-small uk-table-justify uk-table-divider" -->' +
                ' <tr><td>Artikelanzahl</td><td>' + obj[item]['result']['sum'] + '</td></tr>' +
                ' <tr><td>Durchschnitt/Zeiteinheit</td><td>' + obj[item]['result']['mean'] + '</td></tr>' +
                ' <tr><td>Prozentanteil</td><td>' + obj[item]['result']['percent_mean'] + '</td></tr>' +
                ' <tr><td>Sentiment Analyse (+/- Bewertung)</td><td>' + obj[item]['result']['senti'] + '</td></tr>' +
                ' <tr><td>Sentiment Analyse Durchschnitt </td><td>' + obj[item]['result']['senti_mean_array_mean'] + '</td></tr>' +
                '</table>'
                // + '<button type="button" id="download' + item + '" onclick="done(' + item + ')">IMG</button>' + 
                // '<img id="imgurl' + item + '">'
                ;
            var ctx = [];
            ctx[item] = document.getElementById('chart' + item).getContext('2d');
            var chart = []
            chart[item] = new Chart(ctx[item],
            {
                type: 'bar', // line or bar
                data: {
                    labels: obj[item]['result']['date_array'],
                    datasets: [
                        {
                            label: 'Anzahl der Artikel zu ' + obj[item]['meta']['topic_comb'] ,
                            data: obj[item]['result']['count_array'],
                            type: 'line'
                        },
                        {
                            label: 'Prozent der Gesamtzahl',
                            data: obj[item]['result']['percent_array'],
                            type: 'line'
                        },
                        {
                            label: 'Sentimental Analysis Gesamt',
                            data: obj[item]['result']['senti_array'],
                            type: 'line'
                        },
                        {
                            label: 'Sentim. Analysis Durchschnitt/Zeiteinheit',
                            data: obj[item]['result']['senti_mean_array'],
                            type: 'line'
                        },
                        {
                            label: 'Gesamtzahl der Artikel',
                            data: obj[item]['total']['count_array'],
                            type: 'line',
                            hidden: hidetotal
                        },
                        {
                            label: 'Events',
                            data: obj[item]['events']['events_array']
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    elements: {
                        line: {
                            tension: 0.2 // disables bezier curves
                        }
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'tableau.Tableau10'
                        }
                    },
                    scales: {
                        // xAxes: [{
                        //     type: 'time',
                        //     time: {
                        //         unit: 'month'
                        //     }
                        // }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    // onComplete: done(item),
                    // onComplete: testfunction(chart[item], item)
                    onComplete: chartloaded(item)
                }
            });
            // strange idea
            function testfunction(dat, item) {
                console.log(dat);
                console.log('testf ' + item);
                console.log('chart ' + chart[item]);
                console.log('ctx ' + ctx[item]);
                // var burl = ctx[item].toBase64Image();
                // console.log(burl);
            }
        }
        else {
            console.log('no json ' + item);
            alert('No JSON!');
        }
        // performance monitor
        var t1 = performance.now();
        td = 'T= ' + (t1 - t0) + 'ms ';
        console.log(td);
        document.getElementById('footer').append(' - ' + td);
    }
}

// parseUrls
function parseUrls(item, index) {
    let itopic = Object.keys(data['dashboards'])[index];
    url[index] = 
        urlslugview + 'topic=' + 
        Object.keys(data['dashboards'])[index] +
        data['dashboards'][itopic]['meta'];
    // events
    if (data['dashboards'][itopic]['type'] == 'event') {
        document.getElementById('events').innerHTML += '<li><a href="' + url[index] + '">' + itopic + '</a></li>';        
    }
    // election
    if (data['dashboards'][itopic]['type'] == 'election') {
        document.getElementById('elections').innerHTML += '<li><a href="' + url[index] + '">' + itopic + '</a></li>';        
    }
    // < 2015
    if (data['dashboards'][itopic]['type'] == 'election-germany' && data['dashboards'][itopic]['date'] < '2014') {
        document.getElementById('ele2015less').innerHTML += '<li><a href="' + url[index] + '">' + itopic + '</a></li>'; 
    }
    // > 2015
    if (data['dashboards'][itopic]['type'] == 'election-germany' && data['dashboards'][itopic]['date'] >= '2014') {
        document.getElementById('ele2015more').innerHTML += '<li><a href="' + url[index] + '">' + itopic + '</a></li>'; 
    }
}

// // displayMeta
// function displayMeta(item, index) {
//     // if there's time, make meta foreach
// }

// // download img
// function done(item, index){
//     console.log(item);
//     canvasname = 'chart' + item;
//     console.log(canvasname);
//     var canvas = document.getElementById(canvasname);
//     var dataURL = canvas.toDataURL();
//     console.log(dataURL);
//     var dataURL = canvas.toDataURL("image/jpeg", 1.0);
//     console.log(dataURL);
//     // var b64 = canvas.toBase64Image();
//     // console.log(b64);
//     // alert(item);
//     // var imgurl = [];
//     // imgurl[item]= document.getElementById('chart' + item).toBase64Image();
//     // document.getElementById('imgurl' + item).src=imgurl[item];
// }

function chartloaded (item) {
    if (obj[item]['events']['events_full_array'] != 0) {
        addEventsArray(item);
    }
    addSingleUrl(item);
}

function addSingleUrl (item) {
    var singleurl = [];
    singleurl[item] = urlslugsingle + 'topic=' + topics + '&start=' + obj[item]['meta']['start'] + '&end=' + obj[item]['meta']['end'];
    document.getElementById('cardfooter' + item).innerHTML += 
    '<a class="uk-button uk-button-text" href="' + urlslugexport + 'export-' + obj[item]['meta']['topic_comb_array']['0'] + '.csv" target="_blank">Export data as CSV</a>' +
    ' &nbsp;&nbsp; ' + 
    '<a class="uk-button uk-button-text" href="' + singleurl[item] + '" target="_blank">Display items via JSON API</a>';
    // console.log(obj[item]['events']['events_full_array']);
}

function addEventsArray (item) {
    let table = document.createElement('table');
    table.classList.add('uk-table', 'uk-table-divider');
    document.getElementById('cardfooter' + item).appendChild(table)
    for (let i = 0; i < obj[item]['events']['events_full_array'].length; i++) {
        table.innerHTML += 
            '<tr><td>' + 
                obj[item]['events']['events_full_array'][i]['e_date'] + '</td><td>' + 
                obj[item]['events']['events_full_array'][i]['e_title'] + '</td><td>' + 
                obj[item]['events']['events_full_array'][i]['e_desc'] + '</td><td>' + 
            '</td></tr>';
        document.getElementById('cardfooter' + item).appendChild(table);
    }
}