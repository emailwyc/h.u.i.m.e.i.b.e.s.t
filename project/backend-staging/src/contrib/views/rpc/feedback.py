# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Feedback

rpc_feedback = Blueprint('feedback', __name__, url_prefix='/rpc/feedback')


@rpc_feedback.route('/create', methods=['POST'])
@auth_required('doctor', 'patient')
def create():
    feedback = Feedback()
    if g.role == 'doctor':
        setattr(feedback, 'doctor', g.role_instance)
    elif g.role == 'patient':
        setattr(feedback, 'patient', g.role_instance)
    feedback.content = g.form.get('content')
    feedback.save()
    g.message = '提交成功'
    return display(None)


# vim:ts=4:sw=4
