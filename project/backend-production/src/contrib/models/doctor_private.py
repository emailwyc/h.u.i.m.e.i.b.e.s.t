# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .user import User
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY


class DoctorPrivate(DocumentExtend):

    meta = {
        'collection': 'doctor_private'
    }

    uref = db.ReferenceField(User, required=True, unique=True,
                             dbref=True, db_field='_uref', reverse_delete_rule=ME_DENY)
    security_code = db.StringField()
    idcard = db.StringField()
    certificate = db.ListField(db.StringField())
    # revenue = db.DecimalField(default=0)
    bank_card = db.DictField()
    bank_card_bind = db.BooleanField(default=False)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'bank_card', 'bank_card_bind'])


# vim:ts=4:sw=4
