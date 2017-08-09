# vim: set fileencoding=utf-8 :

from . import *


class CreateForm(Form):
    content = StringField('content', validators=[
        validators.DataRequired(message='反馈内容必填')
    ])


# vim:ts=4:sw=4
