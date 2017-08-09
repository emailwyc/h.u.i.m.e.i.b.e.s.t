# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .user import User
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Doctor(DocumentExtend):

    meta = {
        'collection': 'doctor'
    }

    class _service_provided(db.EmbeddedDocument):
        on = db.BooleanField(required=True)
        price = db.DecimalField()
        price_05 = db.DecimalField()
        price_10 = db.DecimalField()
        price_15 = db.DecimalField()
        price_20 = db.DecimalField()
        quantity = db.IntField()
        minutes_min = db.IntField()

    uref = db.ReferenceField(User, required=True, unique=True,
                             dbref=True, db_field='_uref', reverse_delete_rule=ME_DENY)
    name = db.StringField()
    freeze = db.StringField()
    assistant = db.ObjectIdField()
    # avatar = db.URLField()
    avatar = db.StringField()
    hospital = db.StringField()
    hospital_id = db.ObjectIdField()
    region_id = db.ObjectIdField()
    region_child_id = db.ObjectIdField()
    department = db.StringField()
    department_id = db.ObjectIdField()
    department_child_id = db.ObjectIdField()
    position = db.StringField()
    title = db.StringField()
    # locations = db.ListField()
    speciality = db.StringField()
    description = db.StringField()
    service_provided = db.MapField(db.EmbeddedDocumentField(_service_provided))
    starred = db.IntField(default=0)
    con_num = db.IntField(default=0)
    mul_num = db.IntField(default=0)
    rc_num = db.IntField(default=0)
    reg_num = db.IntField(default=0)
    level = db.IntField(default=0)
    comment = db.DictField(default={'star': 0, 'num': 0, 'per': 100})
    scene_id = db.IntField()

    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'name', 'avatar', 'hospital', 'region_id','regoin_child_id','department', 'department_id',
                    'position', 'title', 'speciality', 'description', 'service_provided', 'starred', 'level'])


# vim:ts=4:sw=4
