
<div id="app-search">


    <div class="form-block frame_container_min" >

        <!--  ...   -->
        <div class="oemBlock">
            <div class="poiskPoOem">
                <span>Поиск по OEM в Красноярске<div>на сайтах автомагазинов</div></span>
            </div>


            <div class="oemRow">
                <input type="text" placeholder="Введите OEM-номер детали" autocomplete="on" id="PriceOem" >
                <button value="" id="myBtn">Найти</button>
            </div>
            <div id="progressbar"></div>

            <div id="error_price"></div>

            <div id="oemResult">
                <div class="result_content"></div>
            </div>
        </div>
        <!--  ...   -->
    </div>

    <script type="text/javascript">

        $().ready(function() {
        $('body').on("click", "#myBtn", function () {
            var temp = $(this);
            var progress = 0;
            var timer;
            var data = '&oem='+ $('#PriceOem').val()+'&city_id='+<?=$a->getCityId()?>;

            $.ajax({
                type: "POST",
                url: site.apiDomain+"index.php?p=f_oem&req=1",
                dataType: "json" ,
                cache: false,
                data: data,

                success: function(data){

                    progress = 0;
                    clearInterval(timer);

                    $('#list_oem').html('');

                    if (data.message) {
                        $('#error_price').html(data.message).css('display',"block");
                    }
                    if ($('#error_price').html() === '') {

                        if ($('#list_oem').size() == 0) {
                            $('.result_content').append('<table border="0" cellspacing="0" cellpadding="0" class="table_e92style table_e92style_no_light" style="text-align" id="list_oem"></table>');
                        }
                        if (data.result == "1") {
                            $(document).ready(function () {
                                if (data.list) {
                                    for (i = 0; i < data.list.length; i++) {
                                        if (data.list[i].party == 1) {
                                            var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

                                            if (!isMobile) {
                                                $('#list_oem').append('<tr class="party-oem" data-id="' + data.list[i].f_id + '">' +
                                                    '<td class="name_firm"><div class="name_firm_name">' + data.list[i].name_commercial +
                                                    '</div><div class="name_firm_address" data-content="' + data.list[i].address + '">&nbsp;' +
                                                    '</div></td><td class="brand">' + data.list[i].brand +
                                                    '</td><td class="cost"><a href="'
                                                    + data.list[i].url_detail + '" target="_blank">' + data.list[i].cost + 'р.</a>' +
                                                    '</td><td data-content="' + data.list[i].telefon + '" class="acc-tel">' +
                                                    '</td><td class="full_name">' + data.list[i].full_name + '</td></tr>');
                                            } else {
                                                $('#list_oem').append('<tr class="party-oem" data-id="' + data.list[i].f_id + '">' +
                                                    '<td class="name_firm"><div class="name_firm_name">' + data.list[i].name_commercial +
                                                    '</div><div class="name_firm_address" data-content="' + data.list[i].address + '">&nbsp;' +
                                                    '</div></td><td class="brand">' + data.list[i].brand +
                                                    '</td><td class="cost_tel"><div class="cost"><a href="'
                                                    + data.list[i].url_detail + '" target="_blank">' + data.list[i].cost + 'р.</a></div><div data-content="' + data.list[i].telefon + '" class="acc-tel"></div>' +
                                                    '</td><td class="full_name">' + data.list[i].full_name + '</td></tr>');
                                            }


                                        } else {
                                            var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

                                            if (!isMobile) {

                                                $('#list_oem').append('<tr class="not-party-oem" data-id="' + data.list[i].f_id + '">' +
                                                    '<td class="name_firm"><div class="name_firm_name">' + data.list[i].name_commercial +
                                                    '</div><div class="name_firm_address" data-content="' + data.list[i].address + '">&nbsp;' +
                                                    '</div></td><td class="brand">' + data.list[i].brand +
                                                    '</td><td class="cost"><a href="'
                                                    + data.list[i].url_detail + '" target="_blank">' + data.list[i].cost + 'р.</a>' +
                                                    '</td><td data-content="' + data.list[i].telefon + '" class="acc-tel">' +
                                                    '</td><td class="full_name">' + data.list[i].full_name + '</td></tr>');
                                            } else {
                                                $('#list_oem').append('<tr class="not-party-oem" data-id="' + data.list[i].f_id + '">' +
                                                    '<td class="name_firm"><div class="name_firm_name">' + data.list[i].name_commercial +
                                                    '</div><div class="name_firm_address" data-content="' + data.list[i].address + '">&nbsp;' +
                                                    '</div></td><td class="brand">' + data.list[i].brand +
                                                    '</td><td class="cost_tel"><div class="cost"><a href="'
                                                    + data.list[i].url_detail + '" target="_blank">' + data.list[i].cost + 'р.</a></div><div data-content="' + data.list[i].telefon + '" class="acc-tel"></div>' +
                                                    '</td><td class="full_name">' + data.list[i].full_name + '</td></tr>');
                                            }

                                        }
                                    }
                                }

                                if (isMobile) {
                                    $('.full_name').each(function () {
                                        $(this).slideToggle('fast');
                                    });
                                    $('.acc-tel').each(function () {
                                        $(this).slideToggle('fast');
                                    });
                                }


                                //разворачивание многоточия у адреса

                                var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

                                $('.name_firm_address').each(function () {
                                    //находим первую заглавную букву - это название улицы
                                    var attr = $(this).attr('data-content');
                                    if (attr !== 'Адрес уточняйте по телефону') {
                                        for (i = 0; i < attr.length; i++) {
                                            if ((attr.charAt(i) == attr.charAt(i).toUpperCase()) && (attr.charAt(i).match(/[А-Яа-яЁё]/i))) {
                                                var pos = i;
                                                break;
                                            }
                                        }
                                        //находим первую цифру
                                        for (i = 0; i < attr.length; i++) {
                                            if (attr.charAt(i).match(/[0-9]/)) {
                                                var pos_dig = i;
                                                break;
                                            }
                                        }
                                        //если цифра встретилась раньше первой заглавной буквы, значит цифра входит в название улицы
                                        if (pos_dig < pos) {
                                            var address = attr.substring(pos_dig);
                                            $(this).attr('data-content', address);
                                        } else {
                                            var address = attr.substring(pos);
                                            $(this).attr('data-content', address);
                                        }
                                        
                                        var address_new = attr.substring(pos);
                                        for (i = 0; i < address_new.length; i++) {
                                            if (address_new.charAt(i).match(/[0-9]/)) {
                                                var pos_dig2 = i;
                                                break;
                                            }
                                        }

                                        //оставляем только название улицы
                                        if (pos_dig > pos) pos = 0;
                                        address_new = address.substring(0, pos_dig2 + pos);

                                        var strArr = ['д.', 'Д.', 'пав.', 'Пав.', 'пав', 'вл.', 'Вл.', 'владение', 'Владение',
                                        'стр.', 'Стр.', 'стр', 'Стр',];
                                        for (i = 0; i < strArr.length; i++) {
                                            if (address_new.indexOf(strArr[i]) >= 0) {
                                                var last_pos = address_new.indexOf(strArr[i]);
                                                address_new = address.substring(0, last_pos);
                                                break;
                                            }
                                        }

                                        var end = address_new.substr(-3, 3);
                                        end = end.replace(/[^А-Яа-яЁё|»)]/gi, '');

                                        address_new = address_new.substring(0, address_new.length - 3) + end.substring(0);
                                        $(this).html(address_new + '...');
                                        $(this).attr('data-short', address_new);
                                       
                                    } else {
                                        $(this).html(attr);
                                    }
                                });

                                $('.name_firm_address').on('click', function () {
                                    var attr = $(this).attr('data-content');
                                    if (attr !== 'Адрес уточняйте по телефону') {
                                        var attr_short = $(this).attr('data-short');
                                        $(this).toggleClass('in');
                                       
                                        if ($(this).hasClass('in')) {
                                            $(this).html(attr);
                                        } else {
                                            $(this).html(attr_short + '...');
                                        }
                                    }
                                });

                                //разворачивание многоточия у телефона

                                $('.acc-tel').each(function () {
                                    var attr = $(this).attr('data-content');
                                    $(this).html(attr.substring(0, 11) + '...');
                                });

                                $('.acc-tel').on('click', function () {
                                    var attr = $(this).attr('data-content');
                                    $(this).toggleClass('in');
                                    if ($(this).hasClass('in')) {
                                        $(this).html(attr);
                                    } else {
                                        $(this).html(attr.substring(0, 11) + '...');
                                    }
                                });


                                //сортировка бренда в алфавитном порядке, строки с пустым брендом вниз

                                var list_oem = document.getElementById('list_oem');
                                var tbody = list_oem.getElementsByTagName('tbody')[0];
                                var party = tbody.getElementsByClassName(['party-oem']);
                                var partyArray = Array.from(party);

                                function compareRandom(a, b) {
                                    return Math.random() - 0.5;
                                }

                                partyArray.sort(compareRandom);

                                var notParty = tbody.getElementsByClassName(['not-party-oem']);
                                var notPartyArray = Array.from(notParty);

                                notPartyArray.sort(function (rowA, rowB) {
                                    if ((rowA.cells[1].innerHTML > rowB.cells[1].innerHTML) || (rowA.cells[1].innerHTML === '')) {
                                        return 1;
                                    }
                                    if (rowA.cells[1].innerHTML < rowB.cells[1].innerHTML) {
                                        return -1;
                                    }
                                    return 0;
                                });

                                var noDetail = tbody.getElementsByClassName('no_detail');
                                var notDetailArray = Array.from(noDetail);

                                list_oem.removeChild(tbody);
                                tbody.innerHTML = '';
                                for (var i = 0; i < partyArray.length; i++) {
                                    tbody.appendChild(partyArray[i]);
                                }
                                for (var i = 0; i < notPartyArray.length; i++) {
                                    tbody.appendChild(notPartyArray[i]);
                                }
                                for (var i = 0; i < notDetailArray.length; i++) {
                                    tbody.appendChild(notDetailArray[i]);
                                }
                                list_oem.appendChild(tbody);

                                //в мобильной версии показ/скрытие описания детали и телефона при нажатии на строку

                                if (isMobile) {
                                    $('#list_oem td:not(:has(.acc-tel, .name_firm_address))').on('click', function () {
                                        $(this).parent().find('.full_name').slideToggle("fast");
                                        $(this).parent().find('.acc-tel').slideToggle("fast");
                                        if ($(this).parent().find('.full_name').css('display') !== 'none') {
                                            var acc_tel = $(this).parent().find('.acc-tel');
                                            if (acc_tel.hasClass("in")) {
                                                acc_tel.removeClass("in");
                                                var attr = acc_tel.attr('data-content');
                                                acc_tel.html(attr.substring(0, 11) + '...');
                                            }
                                        }
                                    });
                                }
                            });
                        }

                        if (data.no_detail) {

                            var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

                            if (!isMobile) {
                                for (i = 0; i < data.no_detail.length; i++) {
                                    $('#list_oem').append('<tr class="no_detail" data-id="' + data.no_detail[i].f_id + '">' +
                                        '<td class="name_firm"><div class="name_firm_name">' + data.no_detail[i].name_commercial +
                                        '</div></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align: right">нет</td></tr>');
                                }
                            } else {
                                for (i = 0; i < data.no_detail.length; i++) {
                                    $('#list_oem').append('<tr class="no_detail" data-id="' + data.no_detail[i].f_id + '">' +
                                        '<td class="name_firm"><div class="name_firm_name">' + data.no_detail[i].name_commercial +
                                        '</div></td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align: right">нет</td></tr>');
                                }
                            }

                        }
                        $('.result_content').show();
                    }

                },

                error: function(XMLHttpRequest, textStatus, errorThrown){
                    $('#error_message_reply').html('Ошибка запроса. Проверьте своё подключение к интернету! Если ошибка повторяется обратитесь к администратору\n'+textStatus+"="+errorThrown).css('display',"block");
                },
                beforeSend:  function(data){
                    if (!$('#PriceOem').val()) {
                        $('#error_price').html('Вы не ввели OEM').css('display',"block");
                    } else {
                        $('#error_price').empty().css('display','none');
                    }
                    $('#progressbar').show().css('display','inline-block');
                    $('.result_content').hide();
             
                    timer = setInterval(updateProgressbar, 400);

                    function updateProgressbar(){
                        $("#progressbar").progressbar({
                            value: ++progress
                        });
                        if(progress == 100)
                            clearInterval(timer);
                    }
                    $( function() {
                        $( "#progressbar" ).progressbar({
                            value: progress
                        });
                    } );
                },
                complete:  function(data){
                    $('#progressbar').hide();
                }
            });
        });

    });

    </script>
