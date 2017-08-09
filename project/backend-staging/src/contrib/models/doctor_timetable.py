# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY


class DoctorTimetable(DocumentExtend):

    meta = {
        'collection': 'doctor_timetable',
        'indexes': [
            {
                'fields': ['doctor', 'service', 'date', 'interval'],
                'unique': True
            }
        ]
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    service = db.StringField(choices=SERVICE_TYPE, required=True)
    date = db.StringField(required=True)
    interval = db.StringField(required=True)
    weekday = db.StringField(required=True)
    price = db.DecimalField()
    quantity = db.IntField(default=0)
    remain = db.IntField(default=0)
    minutes_quantity = db.IntField(default=0)
    minutes_remain = db.IntField(default=0)
    location = db.DictField()
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'service', 'date', 'interval', 'weekday', 'price', 'quantity', 'remain', 'location'])


# vim:ts=4:sw=4
