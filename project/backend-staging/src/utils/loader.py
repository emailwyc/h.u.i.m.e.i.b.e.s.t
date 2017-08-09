# vim: set fileencoding=utf-8 :

import sys
import importlib


class Loader(object):

    @staticmethod
    def loaded(prefix, detail=False):
        x = list(sys.modules.keys())
        x.sort()
        x = [s for s in x if s.find(prefix) == 0]
        if detail:
            def formatter(string):
                string = str(string)
                string = string.strip('<').strip('>')
                return ' '.join(string.split(' ')[2:])
            _length = len(max(x, key=len)) + 4
            _template = '{0:-<' + str(_length) + 's} {1}'
            x = [_template.format(k + ' ', formatter(sys.modules.get(k))) for k in x]
        return '\n'.join(x)

    @staticmethod
    def form(app_name, module_name, form_name):
        return Loader._loader(app_name, 'forms', module_name, '{0}_form'.format(form_name))

    @staticmethod
    def _loader(app_name, prefix, module_name, class_name=None):
        # 尝试从缓存中返回
        to_load = '{0}.{1}.{2}'.format(app_name, prefix, module_name)
        try:
            module = sys.modules[to_load]
            # print('try: {0}\nmodule: {1}\ncached'.format(to_load, module))
        except KeyError:
            module = importlib.import_module(to_load)
            # print('try   : {0}\nmodule: {1}\nnew import'.format(to_load, module))
        if module:
            if class_name:
                module_name = class_name
            attr = underline_to_camel(module_name)
            if hasattr(module, attr):
                return getattr(module, attr)
        return None


def camel_to_underline(camel_format):
    '''
    驼峰命名格式 -> 下划线命名格式
    '''
    underline_format = ''
    if isinstance(camel_format, str):
        for s in camel_format:
            underline_format += s if s.islower() else '_' + s.lower()
    return underline_format


def underline_to_camel(underline_format):
    '''
    下划线命名格式 -> 驼峰命名格式
    '''
    camel_format = ''
    if isinstance(underline_format, str):
        for s in underline_format.split('_'):
            camel_format += s.capitalize()
    return camel_format


# vim:ts=4:sw=4
