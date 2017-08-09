# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime
from mongoengine.queryset import queryset_manager


class ArticleHot(DocumentExtend):

    meta = {
        'collection': 'article_hot',
    }

    title = db.StringField(required=True)
    posted_date = db.StringField(required=True)
    link_url = db.URLField(required=True)
    image_url = db.URLField(required=True)
    description = db.StringField(required=True)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    @queryset_manager
    def objects(cls, queryset):
        return queryset.order_by('+id')

    def visible(self):
        return set(['id', 'title', 'posted_date', 'link_url', 'image_url', 'description'])


# vim:ts=4:sw=4
