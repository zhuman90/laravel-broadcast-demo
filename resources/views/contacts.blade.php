<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contacts</title>
    <script
        src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
</head>
<body>
<h1>当前用户：<span class="current-user">{{ auth()->user()->name }}</span>
    <input type="text" name="name" placeholder="请输入用户名" class="input-user" style="display: none"
           value="{{ auth()->user()->name }}"/>
    <button class="btn-edit-user">编辑</button>
</h1>
<div class="users">
    @foreach($users as $user)
        @if ($user->id == auth()->user()->id)
            @continue
        @endif
        <div class="user"
             data-name="{{ $user->name }}"
             data-id="{{ $user->id }}"
             style="border-bottom: 1px solid #eee; padding: 10px; margin: 10px;"
        >
            {{ $user->id }}. {{ $user->name }}
        </div>
    @endforeach
</div>

<div class="chat" style=" display: none">
    <div class="messages"></div>
    <div class="sender" style="margin: 20px; background: #eee; padding: 20px;">
        <div>发给：<span class="reply-user"></span></div>
        <input type="hidden" name="user_id"/>
        <input type="text" name="message" placeholder="请输入消息内容"/>
        <button class="back">返回</button>
        <button type="submit" class="send-btn">发送</button>
    </div>
</div>

<div class="notification"
     style="position: fixed; left: 0; top: 0; width: 100%; height: 100%; display: none; justify-content: center; align-items: center;">
    <div class="content" style="padding: 30px; background: rgba(0, 0, 0, 0.5); color: #fff; font-size: 18px;"></div>
</div>


<script>
    const ws = new WebSocket("ws://127.0.0.1:6001/app/broadcast-demo");

    ws.onopen = function (e) {
        console.log('连接成功', e);
    };

    // 监听广播消息
    ws.onmessage = function (e) {
        console.log('onmessage data', e.data);
        const data = e.data ? JSON.parse(e.data) : null;
        if (!data) return;

        const event = data.event;
        const eventData = data.data ? JSON.parse(data.data) : {};
        console.log('eventData', eventData);

        switch (event) {
            case 'pusher:connection_established':
                init(eventData);
                break;

            case 'App\\Events\\MessageEvent':
                onNewMessage(eventData);
                break;

            case 'App\\Events\\UserUpdateEvent':
                onUserUpdate(eventData);
                break;
        }
    }

    let socketToken = null;

    // 初始化
    async function init(data) {
        await auth(data.socket_id);

        // 订阅私有频道
        ws.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: {
                auth: socketToken,
                channel: 'private-users.{{ auth()->user()->id }}'
            }
        }));

        // 订阅公共频道
        ws.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: {
                channel: 'public'
            }
        }));
    }

    // 认证
    function auth(socketId) {
        return new Promise((resolve, reject) => {
            $.ajax({
                method: 'POST',
                url: '/broadcasting/auth',
                data: {
                    _token: '{{ csrf_token() }}',
                    socket_id: socketId,
                    channel_name: 'private-users.{{ auth()->user()->id }}'
                },
                success: function (data) {
                    socketToken = data.auth;
                    resolve(data);
                },
            });
        })
    }

    let newMessageTimer = null;
    // 有新消息
    function onNewMessage(data) {
        clearTimeout(newMessageTimer);
        $('.chat .messages').append(`<div class="item item-${data.from_user.id}">${data.from_user.name}：${data.message}</div>`);
        $('.notification .content').html(`${data.from_user.name}：${data.message}`);

        $('.notification').show().css("display", "flex");
        newMessageTimer = setTimeout(() => {
            $('.notification').hide();
        }, 2000);
    }

    // 用户信息变更
    function onUserUpdate(data) {
        if (data.id === {{ auth()->user()->id }}) return;
        $(`.users .user[data-id="${data.id}"]`).html(`${data.id}. ${data.name}`);
    }

    $(function () {
        $('.user').on('click', function () {
            const userId = $(this).data('id');
            $('input[name="user_id"]').val(userId);
            $('.reply-user').html($(this).data('name'));
            $('.chat').show();
            $('.users').hide();
            $(`.chat .messages .item`).hide();
            $(`.chat .messages .item-${userId}`).show();
        })

        $('.back').on('click', function () {
            $('.chat').hide();
            $('.users').show();
        });

        $('.send-btn').on('click', function () {
            $.ajax({
                method: 'POST',
                url: '/contacts/send',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: $('.sender input[name="user_id"]').val(),
                    message: $('.sender input[name="message"]').val(),
                },
                success: function (data) {
                    console.log(data);
                    $('.chat .messages').append(`<div class="item item-${data.from_user.id}">${data.from_user.name}：${data.message}</div>`);
                    $('.sender input[name="message"]').val('');
                }
            });
        })

        $('.btn-edit-user').on('click', function () {
            const input = $('.input-user');
            if (input.is(':visible')) {
                $.ajax({
                    method: 'PUT',
                    url: '/user',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: input.val(),
                    },
                    success: function (data) {
                        console.log(data);
                        $('.current-user').html(data.name);
                        input.hide();
                    }
                });
            } else {
                input.show();
            }
        })
    });
</script>
</body>
</html>
