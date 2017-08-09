# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend

class OrderQrcode(DocumentExtend):

    meta = {
        'collection':'order_qrcode',
    }

    price = db.DecimalField(required=True)
    seq = db.StringField()
    desc = db.StringField()
    service = db.StringField()
    status = db.IntField(required=True)
    created_at = db.IntField()
    updated_at = db.IntField()
    pay_at = db.IntField()

    def visible(self):
        return set(['price', 'seq', 'desc', 'status','created_at','pay_at'])


# vim:ts=4:sw=4
