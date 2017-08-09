# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime
from mongoengine.queryset import queryset_manager


class OrderComment(DocumentExtend):

    meta = {
        'collection': 'order_comment'
    }

    doctor = db.StringField()
    patient = db.StringField()
    order = db.StringField()
    patient_name = db.StringField(db_field='p_name')
    patient_gender = db.StringField(db_field='p_gender')
    service = db.StringField()
    star =  db.IntField()
    content = db.StringField(db_field='msg')
    created_at = db.IntField(db_field='tm')

    def visible(self):
        return set(['id', 'doctor', 'patient', 'order', 'patient_name', 'patient_gender',
                    'service', 'star', 'content', 'created_at'])


# vim:ts=4:sw=4
