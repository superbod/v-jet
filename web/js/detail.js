let PostPage = function(){
    let self = this;
    self.postId = 0;


    self.init = function(){
        self.postId = self.getUrlParams('id');
        self.getPostByID(self.postId);
        self.addCommentAction();
    };

    self.getPostByID = function(postID) {
        $.ajax({
            type: "GET",
            url: "/router.php",
            data: {params:{ action: 'getPost', id: postID}},
            success: function(res) {
                res = JSON.parse(res);
                self.renderPostPage(res['post']);
                self.renderComments(res['comments']);
            }
        });
    };

    self.getUrlParams = function(paramName = ''){
        let params = window
            .location
            .search
            .replace('?','')
            .split('&')
            .reduce(
                function(p,e){
                    var a = e.split('=');
                    p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                    return p;
                },
                {}
            );

        return paramName !== "" ? params[paramName] : params;
    };

    self.renderComments = function(comments) {
        comments.forEach(function(item){
            self.makeCommentBlock(item);
        });
    };

    self.makeCommentBlock = function(item) {
        let commentElement = $('<div>',{
            id: item.id,
            class: 'comment',
        });
        let commentAuthor = $('<span>', {class:'comment-author', text: item['author_name'] + ' - '});
        let commentText = $('<span>', {class: 'comment-text', text: item['comment']});
        commentElement.append(commentAuthor,commentText);
        $('.comments').append(commentElement);
    };

    self.renderPostPage = function(item) {
        let blogElement = $('<div>',{
            id: item.id,
            class: 'detail-element',
        });
        let postName = $('<h3>',{text: item[0]['name']})
        let authorName = $('<div>',{ class: 'detail-author', text: 'Posted By ' + item[0]['author_name']});
        let text = $('<div>',{
            class: "text-block",
            text: item[0]['text']
        });
        let publishDate = $('<div>',{
            class: 'date-block',
            text: item[0]['date']
        });
        let commentsNumber = $('<div>',{
            class: 'comments-number',
            text: 'Total comments '
        });
        let countComments = $('<span>',{
            id:'count-comments',
            text: item[0]['comment_numbers']
        });
        commentsNumber.append(countComments);
        blogElement.append(postName, text, publishDate, commentsNumber, authorName);
        $('.post-block').append(blogElement);
    };

    self.addCommentAction = function() {
        $( "#comment-form" ).on( "submit", function( event ) {
            event.preventDefault();
            let args = {
                blogID: self.postId
            };
            $.each($('#comment-form').serializeArray(), function(i, field) {
                args[field.name] = field.value;
            });

            $.ajax({
                type: "POST",
                url: "/router.php",
                data: {params:{ action: 'createComment', args: args}},
                success: function (res) {
                    self.makeCommentBlock(JSON.parse(res));
                    let countComments = parseInt($('#count-comments').text());
                    $('#count-comments').text(countComments + 1);
                }
            });
        });
    };

    self.init();
};

$(document).ready(function () {
   new PostPage();
});