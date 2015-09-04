$(function(){

    /**
     * @constructor
     */
    var PhotoAlbumWP = function(){

        this.apiUrl = '/PHOTO_ALBUM_API/';
        this.apiAlbumUrl = this.apiUrl + 'album-{albumSlug}/page-{pageNumber}/';
        this.albumRealUrl = '/album/{albumSlug}/';
        this.apiPhotoUrl = this.apiUrl + 'photo-{photoId}/';
        this.photoRealUrl = '/album/photo-{photoId}/';
        this.cssAlbumShowMoreSelector = '.show-more-photos';
        this.cssBgLayerSelector = '.wp-photo-album-bg-layer'; //background darkened layer selector
        this.cssPhotoMountSelector = '.wp-photo-album-item'; // photo mount selector
        this.cssPhotoPopupSelector = '.popup-photo'; // photo mount's parent div selector
        this.cssPhotoPreviewImgSelector = '.photo-preview .photo-thumb img'; // photo preview img selector
        this.cssPhotoCloseSelector = '.photo-close';
        this.photoMountMaxWidth = 654; //photo mount max width in pixels
        this.historyNavigation = {"albumUrl": null, "historyMoves": 0}; // for navigation control
        var _this = this; // for closure
        var photoOpened = false;




        /**
         * return content on photo on mount or null if no photo found or error occured
         *
         * @param id photo identifier
         * @returns null | string (html)
         */
        this.showPhoto = function(id, historySurf){
            if(id>0) {
                var content = null;

                $.ajax({
                    url: _this.apiPhotoUrl.replace(/\{photoId\}/, id),
                    type: 'get',
                    dataType: 'html',
                    async: false,
                    success: function (data) {

                        if(typeof historySurf === 'undefined') {
                            if (_this.historyNavigation.albumUrl === null) {
                                if (!_this.isPhotoRealUrl(window.location.href)) {
                                    _this.historyNavigation.albumUrl = window.location.href;
                                    _this.historyNavigation.historyMoves++;
                                    window.history.pushState(
                                        {"photoId": id, "albumUrl": _this.historyNavigation.albumUrl},
                                        "photo" + id,
                                        _this.photoRealUrl.replace(/\{photoId\}/, id)
                                    );
                                }
                            } else {
                                _this.historyNavigation.historyMoves++;
                                window.history.pushState(
                                    {"photoId": id, "albumUrl": _this.historyNavigation.albumUrl},
                                    "photo" + id,
                                    _this.photoRealUrl.replace(/\{photoId\}/, id)
                                );
                            }
                        }

                        content = data;
                    },
                    error: function () {
                        alert('Error on image get');
                    }
                });

                photoOpened = true;
                return content;
            }
            return null
        };




        this.showAlbum = function(albumSlug, page){
            if(typeof albumSlug == 'undefined'){
                return null;
            }

            var content =  null;
            var url = this.apiAlbumUrl.replace(/\{albumSlug}/, albumSlug).replace(/\{pageNumber}/, page);

            $.ajax({
                url: url,
                type: "GET",
                dataType: "html",
                async: false,
                success: function (data){
                    content = data;
                }
            });

            return content;

        };




        // Close photo and set location href to actual
        this.closePhoto = function(){
            photoOpened = false;
            $(_this.cssBgLayerSelector).hide();
            $(_this.cssPhotoMountSelector).html('');
            $(_this.cssPhotoPopupSelector).hide();

            if(_this.historyNavigation.albumUrl !== null){
                window.history.go(-(_this.historyNavigation.historyMoves));
                _this.historyNavigation.historyMoves = 0;
            }
        };



        // Checks if user listing photos in album or listed url till album base
        this.surfHistory = function(){
            if(_this.historyNavigation.albumUrl == null){
                return;
            }

            if(window.location.href === _this.historyNavigation.albumUrl && photoOpened){
                return _this.closePhoto();
            }

            if(_this.isPhotoRealUrl(window.location.href)){
                var photoId = window.location.href.match(this.photoUrlRegExp())[1];
                $(_this.cssPhotoMountSelector).html(this.showPhoto(photoId, true));
            }
        };



        //Regular expression of single photo url
        this.photoUrlRegExp = function(){
            return _this.photoRealUrl.replace(/\{photoId}/, '(\\d+)');
        };



        //Checks if photo is opened in tab(direct url)
        this.isPhotoRealUrl = function(url){
            var photoUrlPattern = (document.location.origin + this.photoUrlRegExp()).replace(new RegExp(/\//, 'g'), '\\/');
            return url.match(new RegExp(""+photoUrlPattern+"")) !== null;
        };


        this.parseAlbumSlug = function(url){
            var albumSlugRegExp = this.albumRealUrl.replace(/\{albumSlug}/, '(.{3,})?');
            albumSlugRegExp = albumSlugRegExp.replace(new RegExp(/\//, 'g'), '\\/');
            var params = url.match(new RegExp(""+albumSlugRegExp+""));
            return params!==null ? params[1] : false;
        };



        $(this.cssAlbumShowMoreSelector).on('click', function(event){
            var album = _this.parseAlbumSlug(window.location.pathname);
            var page = parseInt($(this).attr('data-page'));
            var photos = null;
            if(album && page>0){
                page++;
                photos = _this.showAlbum(album, page);
                if(photos){
                    $(this).attr('data-page', page);
                    $(this).before(photos);
                } else{
                    $(this).hide();
                }
            } else{
                $(this).hide();
            }
        });

        //On photo mount click shows previous or next photo based on
        // click position(closer to left or right mount border
        $(document).on('click', _this.cssPhotoMountSelector + " .photo-item", function(event){

            var container = $(_this.cssPhotoMountSelector);
            var posX = event.clientX - $(_this.cssPhotoMountSelector).offset().left;
            var nextPhotoId = $("#next-photo").attr('data-target-id');
            var prevPhotoId = $("#previous-photo").attr('data-target-id');
            var photo = null;

            if(container.width()/2 > posX && prevPhotoId > 0){
                photo = _this.showPhoto(prevPhotoId)
            } else if(nextPhotoId > 0){
                photo = _this.showPhoto(nextPhotoId);
            }


            if(photo !== null){
                $(_this.cssPhotoMountSelector).html(photo);
            }

        });


        //On photo preview click shows photo
        $('div').on('click', this.cssPhotoPreviewImgSelector, function(event){
            var photoId = $(this).attr('data-target-id');
            var photoContent;
            event.preventDefault();
            if(typeof photoId !='undefined' && (photoContent = _this.showPhoto(photoId)) !== null) {
                $(_this.cssBgLayerSelector).show();
                $(_this.cssPhotoPopupSelector).show();
                $(_this.cssPhotoMountSelector).css({
                    'maxWidth': _this.photoMountMaxWidth + 'px',
                    'position': 'relative',
                    'z-index': '99999',
                    'top': 10+window.pageYOffset+'px',
                });

                $(_this.cssPhotoMountSelector).html(photoContent);

                var lastScreenOffset = window.pageYOffset;

                window.onscroll = function(e){

                    if(window.pageYOffset<lastScreenOffset){
                        lastScreenOffset = window.pageYOffset;
                        $(_this.cssPhotoMountSelector).css({'top': 10+window.pageYOffset+'px'});
                    }
                };
            }
        });




        //On dark background click hide photo
        $(this.cssBgLayerSelector).on('click', function(event){
            _this.closePhoto();
        });



        //On close button click hide photo
        $(document).on('click',this.cssPhotoCloseSelector, function(event){
            event.stopPropagation();
            _this.closePhoto();
        });




        window.onpopstate = function(event){
            _this.surfHistory();
        };

    };


    new PhotoAlbumWP();

});
