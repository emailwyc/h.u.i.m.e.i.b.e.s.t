# vim: set fileencoding=utf-8 :

from . import *


fields = {
    'service': StringField('service', validators=[
        validators.AnyOf(('clinic', 'phonecall', 'consult'), message='服务类型错误')
    ]),
    'alias': StringField('alias', validators=[
        validators.AnyOf(('schedule-top', 'operation-top'), message='图片别名必填')
    ]),
}


class PriceForm(Form):
    service = fields.get('service')


class QuantityForm(Form):
    service = fields.get('service')


class ImagesForm(Form):
    alias = fields.get('alias')


class HospitalForm(Form):
    keywords = StringField('keywords')


# vim:ts=4:sw=4
