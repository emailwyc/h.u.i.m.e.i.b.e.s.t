# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .patient import Patient
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY
from mongoengine.queryset import queryset_manager


ORDER_STATUS = ('新订单', '等待支付', '付款进行中', '付款失败', '已支付',
                '待就诊', '待转诊', '已转诊', '已就诊', '已完成', '已取消', '已退款', '电话连线中', '电话通话中', '电话通话结束')


ORDER_CALL_STATUS = ('开始问诊', '继续问诊', '连线中', '通话中', '通话结束')



class Order(DocumentExtend):

    meta = {
        'collection': 'order'
    }

    class _voip(db.EmbeddedDocument):
        enable = db.BooleanField(default=True)
        status = db.StringField(choices=ORDER_CALL_STATUS, default='')
        call_sid = db.ListField(default=[])
        record_url = db.DictField()


    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    patient = db.ReferenceField(Patient, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    service = db.StringField(choices=SERVICE_TYPE, required=True)
    schedule = db.DateTimeField(required=True)
    seq = db.StringField(default='')
    location = db.StringField(required=True)
    mobile = db.StringField(required=True)
    name = db.StringField(required=True)
    gender = db.StringField(required=True, choices=[('female', 'Female'), ('male', 'Male')])
    age = db.IntField(required=True)
    idcard = db.StringField(required=True)
    price = db.DecimalField(required=True)
    message = db.StringField(required=True)
    attachments = db.ListField()
    call_minutes = db.IntField(db_field='longTime')
    talk_duration = db.IntField(default=0)
    voip = db.EmbeddedDocumentField(_voip)
    unread = db.BooleanField(required=True, default=True)
    status = db.StringField(required=True, choices=ORDER_STATUS)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    @queryset_manager
    def objects(cls, queryset):
        return queryset.filter(service__in=['consult', 'phonecall']).order_by('+schedule')

    def visible(self):
        return set(['id', 'service', 'schedule', 'seq', 'location', 'mobile',
                    'name', 'gender', 'age', 'idcard', 'price', 'message', 'attachments',
                    'call_minutes', 'talk_duration', 'voip', 'unread', 'status', 'created_at'])


# vim:ts=4:sw=4
