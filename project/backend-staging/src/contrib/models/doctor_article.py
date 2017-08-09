# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from datetime import datetime
from mongoengine import DENY as ME_DENY
from mongoengine.queryset import queryset_manager


class DoctorArticle(DocumentExtend):

    meta = {
        'collection': 'doctor_article',
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=False, reverse_delete_rule=ME_DENY)
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
