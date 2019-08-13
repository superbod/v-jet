let BlogPage = function() {
    let self = this;

    self.init = function(){
        self.getSliderPosts();
        self.getAllPosts();
        self.createPostAction();
    };

    self.createPostAction = function(){
        $( "#post-form" ).on( "submit", function( event ) {
            event.preventDefault();
            let args = {};
            $.each($('#post-form').serializeArray(), function(i, field) {
                args[field.name] = field.value;
            });

            $.ajax({
                type: "POST",
                url: "/router.php",
                data: {params:{ action: 'createPost', args: args}},
                success: function (res) {
                    self.renderBlogListBlock(JSON.parse(res));
                }
            });
        });
    };

    self.getSliderPosts = function(){
        $.ajax({
            type: "GET",
            url: "/router.php",
            data: { params:{ action: 'getSliderPosts', postsNumber: 5 } },
            success: function (res) {
                self.renderSliderBlock(JSON.parse(res));
            }
        });
    };

    self.renderSliderBlock = function(sliderList){
        sliderList.forEach(function(item){
            let text = item.text.length > 1400 ? item.text.slice(0,1400) + ' ...' : item.text;
            let sliderElement = $('<div>',{
                class: 'slider-element',
            });
            let slideName = $('<a>',{
                id: item.id,
                class: "slide-name",
                text: item.name,
                href: 'detail.html?id=' + item.idx
            });
            let slideText = $('<span>',{
                text: text,
                class: 'slide_text'
            });
            sliderElement.append(slideName,slideText);
            $('#blog-slider').append(sliderElement);
        });

        $("#blog-slider").slick({
            slidesToShow: 1,
            slidesToScroll: 1
        });
    };

    self.getAllPosts = function(){
        $.ajax({
            type: "GET",
            url: "/router.php",
            data: {params:{ action: 'getData' }},
            success: function(res) {
                self.renderBlogListBlock(JSON.parse(res));
            }
        });
    };

    self.renderBlogListBlock = function(blogList){
        blogList.forEach(function(item){
            let blogElement = $('<div>',{
                id: item.id,
                class: 'blog-element',
            });
            let postName = $('<a>',{
                class: 'post-name',
                text: item.name,
                href: 'detail.html?id=' + item.id
            });
            let authorName = $('<div>',{ class: 'author-name', text: 'posted by ' + item['author_name']});
            let text = $('<div>',{text: item.text.length > 165 ? item.text.slice(0,165) + ' ...' : item.text});
            let bottomBlock = $('<div>',{class: 'bottom-block'});
            let publishDate = $('<div>',{class: 'publish-date', text: item['date'].slice(0,10)});
            let commentsNumber = $('<div>',{class: 'comments-number', text: item['comment_numbers'] + ' comments'});
            bottomBlock.append(publishDate,commentsNumber,authorName);
            blogElement.append(postName,text,bottomBlock);
            $('.blog-list').append(blogElement);
        });
    };

    self.init();
};

$(document).ready(function () {
    new BlogPage();
});