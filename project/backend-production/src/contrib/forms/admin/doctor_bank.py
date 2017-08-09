# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


class QueryForm(Form):
    order_id = ObjectIdField('订单 ID', filters=(strip, ), validators=[
        validators.DataRequired(message='订单 ID 错误')
    ])


# vim:ts=4:sw=4
