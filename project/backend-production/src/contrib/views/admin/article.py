# vim: set fileencoding=utf-8 :

from .. import *
from ...models import ArticleHot
from ...forms.admin.article import UpdateForm
from flask import render_template, redirect, url_for, flash


admin_article = Blueprint('admin_article', __name__, url_prefix='/admin/article')


@admin_article.route('/show', methods=['GET'])
@http_auth_required
def article_show():
    articles = ArticleHot.objects()
    return render_template('admin/article/show.html', articles=articles)


@admin_article.route('/update', methods=['GET', 'POST'])
@http_auth_required
def article_update():
    form = None
    try:
        form = UpdateForm()
        if request.method == 'POST' and form.validate_on_submit():
            articles = articles_encode(form)
            articles_save(articles)
            flash('精品文章更新成功')
            return redirect('/admin/article/show'.format(id))
    except Exception as e:
        print(e)

    if request.method == 'GET':
        form = UpdateForm()

    articles_decode(form)
    articles_load(form)
    return render_template('admin/article/update.html', form=form)


def articles_encode(form):
    image_url= 'http://hm-img.huimeibest.com/logo.png'
    if not form.data.get('articles'):
        return []
    try:
        data_list = [x.strip(' ') for x in form.data.get('articles').split('\r\n')]
        data_text = '\n'.join(data_list)
        while '\n\n\n' in data_text:
            data_text = data_text.replace('\n\n\n', '\n\n')
        data_list = data_text.split('\n')
        articles = [data_list[i:i+5] for i in range(0, len(data_list), 5)]
        data = []
        for article in articles:
            if len(article) == 5:
                article.pop()
            title, posted_date, link_url, description = article
            data.append({
                'title': title,
                'posted_date': posted_date,
                'link_url': link_url,
                'image_url': image_url,
                'description': description
            })
        form.articles.data = data_list
        return data
    except Exception as e:
        form.articles.errors.append('数据格式错误')
        raise ResourceWarning('数据格式错误')


def articles_decode(form):
    articles = form.data.get('articles')
    if articles and isinstance(articles, list):
        articles = '\r\n'.join(articles)
        form.articles.data = articles


def articles_save(articles):
    ArticleHot.objects().delete()
    for item in articles:
        article = ArticleHot()
        article.title = item.get('title')
        article.posted_date = item.get('posted_date')
        article.link_url = item.get('link_url')
        article.image_url = item.get('image_url')
        article.description = item.get('description')
        article.updated_at = datetime.utcnow()
        article.save()


def articles_load(form):
    if form.articles.data:
        return
    articles = ArticleHot.objects()
    data = []
    for article in articles:
        data.append('\r\n'.join([article.title, article.posted_date, article.link_url, article.description]))
    articles = '\r\n\r\n'.join(data)
    form.articles.data = articles


# vim:ts=4:sw=4
