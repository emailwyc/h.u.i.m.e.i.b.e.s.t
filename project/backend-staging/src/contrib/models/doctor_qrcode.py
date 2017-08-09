# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY
from mongoengine.queryset import queryset_manager


class DoctorQRcode(DocumentExtend):

    meta = {
        'collection': 'doctor_rqcode'
    }

    doctor = db.ReferenceField(Doctor, dbref=True, reverse_delete_rule=ME_DENY)
    scene_id = db.IntField()
    ticket = db.StringField()
    url = db.StringField()
    timestamp = db.IntField()

    @queryset_manager
    def objects(cls, queryset):
        return queryset.order_by('+id')

    def visible(self):
        return set(['doctor', 'scene_id', 'ticket', 'url'])


# vim:ts=4:sw=4
