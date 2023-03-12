{* Smarty *}
<!DOCTYPE html>
<html>

<head>
    <title>{$SERVER_NAME} - Sprays</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div align='center'>
        <div class='header'>
            <div id='pages'>
                <div id='spraypagination'></div>
                <div id='notice'>
                    Displaying <b>{$num_rows}</b> sprays from the last {$deletedays} days.<br>
                    <b>Warning: NSFW</b><br>

                {if $order == 'count' || $order == 'date'}
                    <a href='/sprays/'>Order by upload date</a>&nbsp;|&nbsp;
                {/if}
                {if $order != 'count'}
                    <a href='/sprays/?order=count'>Order by amount of times sprayed</a>
                {/if}
                {if $order != 'count' && $order != 'date'}
                    &nbsp;|&nbsp;
                {/if}
                {if $order != 'date'}
                    <a href='/sprays/?order=date'>Order by most recently sprayed</a>
                {/if}

                {if $num_rows == 0}
                    <p><i>No sprays found</i></p>
                {/if}
                </div>
            </div>
            <div id='login'>
                <form action="" method="post">
                    Username: <input type="text" name="username" size="20"><br>
                    <div style="height:5px;"></div>
                    Password: <input type="password" name="password" size="20">
                    <div style="height:5px;"></div>
                    <input type="submit" value="Login">
                </form>
            </div>
        </div>
        <br>
        <div id='sprays'>
            <div class='spraypage'>
            {foreach $sprays as $assoc}
                <div class='spray'>
                    <img
                        srb='{($assoc['banned']) ? 'badspray.png' : "img/{$assoc['filename']}.png"}'
                        alt=''
                        width='256'
                        height='256'
                        title='<strong>Uploaded: </strong>{$assoc['firstdate_ts']|date_format:'%Y/%m/%d %H:%M:%S'}<br>
                               <strong>Last sprayed: </strong>{($assoc['date_ts']) ? ($assoc['date_ts']|date_format:'%Y/%m/%d %H:%M:%S') : 'Never'}<br>
                               <strong>Last server: </strong>{$assoc['server']}<br>
                               <strong>Sprayed: </strong>{$assoc['count']} {($assoc['count'] <= 1) ? 'time' : 'times'}
                               {($assoc['banned']) ? '<br><strong>Admins have blocked the spray from viewing.</strong>' : ''}'>
                    <div>
                        <a href='http://steamcommunity.com/profiles/{$assoc['steamid64']}'>{$assoc['name']}</a>
                        &mdash;
                        <a style='font-size: 11px' href='javascript:copy2clipboard("{$assoc['steamid']}")'>copy</a>
            {if $_SESSION['logged_in']}
                {if $assoc['banned']}
                        &mdash;
                        <a href='?unban=1&steamid={$assoc['steamid']}&filename={$assoc['filename']}' alt="\"
                        title='Manager-only option - Unblocks converted spray and Unblocks the spray from being used in the future'
                        style='font-size: 11px'>
                        Unblock
                        </a>
                        &mdash;
                        <a href='img/{$assoc['filename']}.png' alt='\' target='_blank'
                        title='Manager-only option - Redirects to blocked spray' style='font-size: 11px'>
                        Click to show.
                        </a>
                {else}
                        &mdash; <a href='?ban=1&steamid={$assoc['steamid']}&filename={$assoc['filename']}'
                        title='Blocks the spray from being used in the future' style='font-size: 11px'>Block</a>
                {/if}
                    
            {/if}
                    </div>
                </div>
            {if $assoc['onpage'] == 23}
            </div>
            <div class='spraypage'>
            {/if}
            {/foreach}
            </div>
        </div>
    </div>

    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.paginate.js'></script>
    <script type='text/javascript' src='js/jquery.tooltip.js'></script>
    <script type='text/javascript'>
        // <![CDATA[
        $(document).ready(function() {
            $("#spraypagination").paginate({
                count: $('#sprays .spraypage').length,
                start: 1,
                display: 15,
                border: true,
                border_color: '#ccc',
                text_color: '#333',
                background_color: '#eee',
                border_hover_color: '#aaa',
                text_hover_color: '#000',
                background_hover_color: '#fff',
                images: true,
                mouse: 'press',
                onChange: function(page) {
                    $(".spraypage").hide().eq(page - 1).show();
                    $(".spraypage:visible img").each(function() {
                        $(this).attr("src", $(this).attr("srb"));
                    });
                }
            });
            $(".spraypage").eq(0).show();
            $(".spray > img").tooltip({
                delay: 0,
                track: true,
                showURL: false
            });
            $(".spraypage:visible img").each(function() {
                $(this).attr("src", $(this).attr("srb"));
            });
        });

        function copy2clipboard(str) {
            navigator.clipboard.writeText(str);
        }
        // ]]>
    </script>
</body>

</html>