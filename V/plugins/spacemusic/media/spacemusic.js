TSC.spacemusic = {
    list: [], //the list of songfiles
    /**
     * fetches the current song list from the server
     */
    getSongList: function (callback) {
        $.ajax({
            url: "ajax.php?controller=spacemusic&action=songlist",
            dataType: "json",
            success: function (data) {
                TSC.spacemusic.list = data;
                if (typeof callback === "function") {
                    //just for the first time called by TSC.spacemusic.createPlayer to start playing
                    callback();
                }
            }
        });
    },
    /**
     * switches to the next song and plays it
     */
    nextTrack: function () {
        TSC.spacemusic.getSongList();
        var player = $("#spacemusic_player")[0];
        var next = 0;
        for(var i = 0; i < TSC.spacemusic.list.length; i++) {
            var adress = decodeURIComponent(player.src);
            if (adress.indexOf(TSC.spacemusic.list[i]) !== -1) {
                next = (i + 1) % TSC.spacemusic.list.length;
                break;
            }
        }
        player.src = TSC.spacemusic.list[next];
        player.play();
    },
    /**
     * creates the html5 audio element with id = "spacemusic_player"
     */
    createPlayer: function () {
        var player = document.createElement('audio');
        player.setAttribute('id', 'spacemusic_player');
        $("body").append(player);
        $("#spacemusic_player")
            .bind("ended", TSC.spacemusic.nextTrack)
            .hide();
        TSC.spacemusic.getSongList(TSC.spacemusic.autoplay ? TSC.spacemusic.nextTrack : null);
    },
    /**
     * stops or plays the music dependend on the boolean value
     * @play boolean : true for playing and false for stopping
     */
    play_pause: function (play) {
        var player = $("#spacemusic_player")[0];
        if (play) {
            if (!player.src && TSC.spacemusic.list.length) {
                //if it's the first time to play the music in this session
                player.src = TSC.spacemusic.list[0];
            }
            player.play();
        } else {
            player.pause();
        }
        $.ajax({
            url: "ajax.php?controller=spacemusic&action=set_autoplay",
            data: {
                autoplay: play ? 1 : 0
            }
        });
    }
};

$(TSC.spacemusic.createPlayer);