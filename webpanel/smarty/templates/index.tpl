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
                    |&nbsp;<a href='/sprays/'>Order by upload date</a>&nbsp;
                {/if}
                {if $order != 'count'}
                    |&nbsp;<a href='/sprays/?o=count'>Order by amount of times sprayed</a>&nbsp;
                {/if}
                {if $order != 'date'}
                    |&nbsp;<a href='/sprays/?o=date'>Order by most recently sprayed</a>&nbsp;
                {/if}
                    |

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
        {$pages = 0}
        {foreach $sprays as $assoc}
            {if gettype($assoc) == 'integer'}
                {$pages = $assoc['pages']}
            {/if}
            {if $assoc['onpage'] == 0}
            <div class='spraypage'>
            {/if}
                <div class='spray'>
                    <img
                    {if $assoc['pages'] == 1}
                        src="{$assoc['img_src']}"
                    {else}
                        srb="{$assoc['img_src']}"
                    {/if}
                        alt=''
                        width='256'
                        height='256'
                        title='<strong>Uploaded:</strong>{$assoc['row']['firstdate_ts']|date_format:'%Y/%m/%d %H:%M:%S'}<br>
                    {if $assoc['row']['date_ts']}
                               <strong>Last sprayed:</strong>{$assoc['row']['date_ts']|date_format:'%Y/%m/%d %H:%M:%S'}<br>
                    {else}
                               <strong>Last sprayed:</strong>Never<br>
                    {/if}
                               <strong>Last server: </strong>{$assoc['server']}<br>
                               <strong>Sprayed:</strong><?= $count ?>
                    {if $assoc['row']['banned']}
                        <br><strong>Admin blocked spray view</strong>
                    {/if}'
                    >
                    <div>
                        <a href='http://steamcommunity.com/profiles/{$assoc['steamid64']}'>{$assoc['row']['name']}</a>
                        &mdash;
                        <a style='font-size: 11px' href='javascript:copy2clipboard("{$assoc['row']['steamid']}")'>copy</a>
            {if $_SESSION['logged_in']}
                {if $assoc['row']['banned']}
                        &mdash;
                        <a href='?unban=1&steamid={$assoc['row']['steamid']}&filename={$assoc['row']['filename']}' alt="\"
                        title='Manager-only option - Unblocks converted spray and Unblocks the spray from being used in the future'
                        style='font-size: 11px'>
                        Unblock
                        </a>
                        &mdash;
                        <a href="{$assoc['img']}" alt="\"
                        title='Manager-only option - Redirects to blocked spray' style='font-size: 11px'>
                        Click to show.
                        </a>
                {else}
                        &mdash; <a href='?ban=1&steamid={$assoc['row']['steamid']}&filename={$assoc['row']['filename']}'
                        title='Blocks the spray from being used in the future' style='font-size: 11px'>Block</a>
                {/if}
                    
            {/if}
                    </div>
                </div>
            {if $assoc['onpage'] == 0}
            </div>
            {/if}
        {/foreach}
        </div>


    </div>

    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.paginate.js'></script>
    <script type='text/javascript' src='js/jquery.tooltip.js'></script>
    <script type='text/javascript'>
        // <![CDATA[
        $(document).ready(function() {
            $("#spraypagination").paginate({
                count: parseInt("<?= $pages ?>", 10) || 1,
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
        });

        function copy2clipboard(str) {
            navigator.clipboard.writeText(str);
        }
        // ]]>
    </script>

</body>

</html>